<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class Atlas
{
    public const METRICS = ['idhm', 'idhm_educacao', 'idhm_renda', 'idhm_longevidade', 'renda_per_capita'];

    public function query(array $filters): Builder
    {
        return DB::table('observations as o')->join('territories as t', 't.code', '=', 'o.territory_code')
            ->when($filters['region'] ?? null, fn ($q, $v) => $q->where('t.region', $v))
            ->when($filters['uf'] ?? null, fn ($q, $v) => $q->where('t.uf', $v));
    }

    public function dashboard(array $filters): array
    {
        $year = (int) ($filters['year'] ?? 2010);
        $metric = $filters['metric'] ?? 'idhm';
        $all = $this->query($filters)->select('t.code', 't.name', 't.uf', 't.region', 'o.year', ...array_map(fn ($m) => 'o.'.$m, self::METRICS))->orderBy('t.name')->get()->map(function ($row) {
            $row = (array) $row;
            foreach (self::METRICS as $m) {
                $row[$m] = $row[$m] === null ? null : (float) $row[$m];
            }
            $row['year'] = (int) $row['year'];

            return $row;
        })->all();
        $rows = array_values(array_filter($all, fn ($r) => $r['year'] === $year));
        $history = [];
        foreach ([1991, 2000, 2010] as $y) {
            $observed = array_values(array_filter($all, fn ($r) => $r['year'] === $y));
            $history[] = ['year' => $y, 'value' => Statistics::quantile(array_column($observed, $metric), .5), 'n' => count(array_filter($observed, fn ($r) => $r[$metric] !== null))];
        }
        $grouped = [];
        foreach ($rows as $r) {
            if ($r[$metric] !== null) {
                $grouped[$r['region']][] = $r[$metric];
            }
        }
        $distribution = [];
        foreach (['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul'] as $name) {
            $v = $grouped[$name] ?? [];
            if ($v) {
                $distribution[] = ['name' => $name, 'n' => count($v), 'values' => array_map(fn ($p) => Statistics::quantile($v, $p), [0, .25, .5, .75, 1])];
            }
        }
        $byCode = [];
        foreach ($all as $r) {
            $byCode[$r['code']][$r['year']] = $r;
        }
        $growth = [];
        foreach ($byCode as $years) {
            if (isset($years[1991], $years[2010]) && $years[1991][$metric] !== null && $years[2010][$metric] !== null) {
                $growth[] = ['code' => $years[2010]['code'], 'name' => $years[2010]['name'], 'uf' => $years[2010]['uf'], 'start' => $years[1991][$metric], 'end' => $years[2010][$metric], 'delta' => round($years[2010][$metric] - $years[1991][$metric], 6)];
            }
        }
        usort($growth, fn ($a, $b) => ($b['delta'] <=> $a['delta']) ?: strcmp($a['code'], $b['code']));
        $selected = array_values(array_filter([$filters['a'] ?? '', $filters['b'] ?? '']));
        $selectedHistory = array_values(array_filter($all, fn ($r) => in_array($r['code'], $selected, true)));
        $valid = array_values(array_filter($rows, fn ($r) => $r[$metric] !== null));
        $ufSummary = [];
        foreach (array_unique(array_column($rows, 'uf')) as $uf) {
            $members = array_values(array_filter($rows, fn ($r) => $r['uf'] === $uf));
            $values = array_values(array_filter(array_column($members, $metric), fn ($v) => $v !== null));
            $ufSummary[] = ['uf' => $uf, 'n' => count($values), 'count' => count($members), 'median' => Statistics::quantile($values, .5)];
        }
        usort($ufSummary, fn ($a, $b) => strcmp($a['uf'], $b['uf']));

        return [
            'year' => $year, 'metric' => $metric, 'rows' => $rows,
            'summary' => ['count' => count($rows), 'valid' => count($valid), 'missing' => count($rows) - count($valid), 'median' => Statistics::quantile(array_column($rows, $metric), .5)],
            'history' => $history, 'selectedHistory' => $selectedHistory, 'growth' => $growth,
            'distribution' => $distribution,
            'ufSummary' => $ufSummary,
            'association' => Statistics::pearson(array_map(fn ($r) => [$r['renda_per_capita'], $r['idhm_educacao']], $rows)),
            'provenance' => ['source' => 'Atlas Brasil — PNUD, Ipea e Fundação João Pinheiro', 'url' => 'https://www.atlasbrasil.org.br/', 'years' => [1991, 2000, 2010], 'incomeUnit' => 'R$ a preços de agosto de 2010'],
        ];
    }
}
