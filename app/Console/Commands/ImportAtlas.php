<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportAtlas extends Command
{
    protected $signature = 'atlas:import {--file= : Local normalized CSV path} {--manifest= : Associated JSON provenance manifest}';

    protected $description = 'Validate and atomically replace the Atlas dataset from a local CSV';

    public function handle(): int
    {
        $path = $this->option('file') ?: base_path('data/atlas_municipios.csv');
        if (! is_file($path)) {
            $this->error('Base não encontrada. Execute o script em scripts/data primeiro.');

            return self::FAILURE;
        }
        $manifestPath = $this->option('manifest') ?: base_path('data/manifest.json');
        if (! is_file($manifestPath)) {
            $this->error('Manifesto obrigatório não encontrado.');

            return self::FAILURE;
        }
        $manifest = file_get_contents($manifestPath);
        try {
            $metadata = json_decode($manifest, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $this->error('Manifesto JSON inválido.');

            return self::FAILURE;
        }
        $expectedHash = $metadata['output']['sha256'] ?? '';
        $expectedUnits = $metadata['coverage']['expected_municipal_units'] ?? 0;
        $expectedYears = $metadata['coverage']['years'] ?? [];
        if (! is_string($expectedHash) || ! preg_match('/^[a-f0-9]{64}$/', $expectedHash)
            || ! hash_equals($expectedHash, hash_file('sha256', $path))
            || ! is_int($expectedUnits) || $expectedUnits < 1 || $expectedYears !== [1991, 2000, 2010]) {
            $this->error('CSV incompatível com o checksum ou a cobertura do manifesto. A base anterior foi preservada.');

            return self::FAILURE;
        }
        $handle = fopen($path, 'r');
        $columns = fgetcsv($handle, 0, ',', '"', '');
        $required = ['codigo_ibge', 'municipio', 'uf', 'regiao', 'ano', 'idhm', 'idhm_educacao', 'idhm_renda', 'idhm_longevidade', 'renda_per_capita'];
        if ($columns !== $required) {
            fclose($handle);
            $this->error('Cabeçalho inválido; use o contrato do CSV normalizado.');

            return self::FAILURE;
        }
        $count = 0;
        try {
            DB::transaction(function () use ($handle, $columns, $path, $manifest, $expectedUnits, &$count) {
                DB::table('observations')->delete();
                DB::table('territories')->delete();
                DB::table('datasets')->delete();
                $id = DB::table('datasets')->insertGetId(['name' => 'Atlas Brasil 1991–2010', 'sha256' => hash_file('sha256', $path), 'manifest' => $manifest, 'imported_at' => now()]);
                $territories = [];
                $batch = [];
                while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                    if (count($line) !== count($columns)) {
                        throw new RuntimeException('Linha com colunas inválidas.');
                    }
                    $row = array_combine($columns, $line);
                    if (! preg_match('/^\d{7}$/', $row['codigo_ibge']) || ! in_array((int) $row['ano'], [1991, 2000, 2010], true)
                        || ! preg_match('/^[A-Z]{2}$/', $row['uf']) || ! in_array($row['regiao'], ['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul'], true)) {
                        throw new RuntimeException('Código, período ou território inválido na linha '.($count + 2));
                    }
                    $record = ['dataset_id' => $id, 'territory_code' => $row['codigo_ibge'], 'year' => (int) $row['ano']];
                    foreach (['idhm', 'idhm_educacao', 'idhm_renda', 'idhm_longevidade', 'renda_per_capita'] as $metric) {
                        $v = $row[$metric] === '' ? null : $row[$metric];
                        if ($v !== null && (! is_numeric($v) || (float) $v < 0 || ($metric !== 'renda_per_capita' && (float) $v > 1))) {
                            throw new RuntimeException('Indicador inválido na linha '.($count + 2));
                        }
                        $record[$metric] = $v === null ? null : (float) $v;
                    }
                    $territories[$row['codigo_ibge']] = ['code' => $row['codigo_ibge'], 'name' => $row['municipio'], 'uf' => $row['uf'], 'region' => $row['regiao']];
                    $batch[] = $record;
                    if (count($batch) >= 250) {
                        DB::table('territories')->insertOrIgnore(array_values($territories));
                        DB::table('observations')->insert($batch);
                        $territories = $batch = [];
                    }
                    $count++;
                }
                if ($count === 0) {
                    throw new RuntimeException('A importação não pode estar vazia.');
                }
                if ($batch) {
                    DB::table('territories')->insertOrIgnore(array_values($territories));
                    DB::table('observations')->insert($batch);
                }
                if ($count !== $expectedUnits * 3 || DB::table('territories')->count() !== $expectedUnits) {
                    throw new RuntimeException('Cobertura diferente da declarada no manifesto.');
                }
                foreach ([1991, 2000, 2010] as $year) {
                    if (DB::table('observations')->where('year', $year)->count() !== $expectedUnits) {
                        throw new RuntimeException('Cobertura anual incompleta.');
                    }
                }
            });
        } catch (\Throwable $e) {
            $this->error('Importação revertida: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            fclose($handle);
        }
        $this->info("{$count} observações importadas com integridade transacional.");

        return self::SUCCESS;
    }
}
