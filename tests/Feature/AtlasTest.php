<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AtlasTest extends TestCase
{
    use RefreshDatabase;

    private function seedAtlas(): void
    {
        $id = DB::table('datasets')->insertGetId(['name' => 'test', 'sha256' => str_repeat('a', 64), 'manifest' => '{}', 'imported_at' => now()]);
        DB::table('territories')->insert([
            ['code' => '3550308', 'name' => 'São Paulo', 'uf' => 'SP', 'region' => 'Sudeste'],
            ['code' => '2927408', 'name' => 'Salvador', 'uf' => 'BA', 'region' => 'Nordeste'],
        ]);
        foreach (['3550308' => [.4, .6, .8], '2927408' => [null, .5, .7]] as $code => $values) {
            foreach ([1991, 2000, 2010] as $i => $year) {
                DB::table('observations')->insert(['dataset_id' => $id, 'territory_code' => $code, 'year' => $year, 'idhm' => $values[$i], 'idhm_educacao' => $values[$i], 'idhm_renda' => $values[$i], 'idhm_longevidade' => $values[$i], 'renda_per_capita' => $values[$i] === null ? null : $values[$i] * 1000]);
            }
        }
    }

    public function test_dashboard_preserves_nulls_and_uses_matched_temporal_pairs(): void
    {
        $this->seedAtlas();
        $response = $this->getJson('/api/dashboard?year=1991&a=3550308&b=2927408');
        $response->assertOk()->assertJsonPath('summary.count', 2)->assertJsonPath('summary.valid', 1)->assertJsonPath('summary.missing', 1);
        $this->assertEquals(.4, $response->json('summary.median'));
        $this->assertCount(1, $response->json('growth'));
        $this->assertEqualsWithDelta(.4, $response->json('growth.0.delta'), 1e-9);
        $this->assertCount(6, $response->json('selectedHistory'));
    }

    public function test_invalid_filters_are_rejected_and_empty_intersections_are_explicit(): void
    {
        $this->seedAtlas();
        foreach (['year=2026', 'metric=sql', 'a=../../.env', 'uf=ZZ', 'region=Outro'] as $query) {
            $this->getJson('/api/dashboard?'.$query)->assertUnprocessable();
        }
        $this->getJson('/api/dashboard?year=2010&region=Sul&uf=SP')->assertOk()->assertJsonPath('summary.count', 0)->assertJsonPath('summary.median', null);
    }

    public function test_export_is_scoped_and_neutralizes_spreadsheet_formulas(): void
    {
        $this->seedAtlas();
        DB::table('territories')->where('code', '3550308')->update(['name' => '=HYPERLINK("unsafe")']);
        $response = $this->get('/api/export?year=2010&uf=SP');
        $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString('Salvador', $csv);
        $this->assertStringNotContainsString('1991', $csv);
    }

    public function test_failed_import_preserves_previous_dataset(): void
    {
        $this->seedAtlas();
        $file = tempnam(sys_get_temp_dir(), 'atlas-test-');
        $manifest = tempnam(sys_get_temp_dir(), 'atlas-manifest-');
        file_put_contents($file, "codigo_ibge,municipio,uf,regiao,ano,idhm,idhm_educacao,idhm_renda,idhm_longevidade,renda_per_capita\n3550308,Teste,SP,Sudeste,2010,2,0.8,0.8,0.8,100\n");
        file_put_contents($manifest, json_encode(['output' => ['sha256' => hash_file('sha256', $file)], 'coverage' => ['expected_municipal_units' => 1, 'years' => [1991, 2000, 2010]]]));
        try {
            $this->artisan('atlas:import', ['--file' => $file, '--manifest' => $manifest])->assertFailed();
            $this->assertSame(6, DB::table('observations')->count());
            $this->assertSame('test', DB::table('datasets')->value('name'));
        } finally {
            unlink($file);
            unlink($manifest);
        }
    }

    public function test_truncated_csv_cannot_reuse_the_national_manifest(): void
    {
        $this->seedAtlas();
        $source = fopen(base_path('data/atlas_municipios.csv'), 'r');
        $file = tempnam(sys_get_temp_dir(), 'atlas-truncated-');
        file_put_contents($file, fgets($source).fgets($source));
        fclose($source);
        try {
            $this->artisan('atlas:import', ['--file' => $file])->assertFailed();
            $this->assertSame(6, DB::table('observations')->count());
        } finally {
            unlink($file);
        }
    }

    public function test_national_import_reconciles_manifest_and_retains_nazaria_nulls(): void
    {
        $this->artisan('atlas:import')->assertSuccessful();
        $this->assertSame(16695, DB::table('observations')->count());
        $this->assertSame(5565, DB::table('territories')->count());
        $this->assertSame(3, DB::table('observations')->where('territory_code', '2206720')->whereNull('idhm')->count());
        $response = $this->getJson('/api/dashboard?year=2010');
        $response->assertOk()->assertJsonCount(27, 'ufSummary')->assertJsonCount(5, 'distribution');
        $this->assertSame(5564, array_sum(array_column($response->json('ufSummary'), 'n')));
        $this->assertEquals(.665, $response->json('summary.median'));
    }

    public function test_content_has_no_drafts_scripts_or_unsafe_links(): void
    {
        $dir = base_path('content/docs/security-test-fixture');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir.'/draft.md', "---\ntitle: Secret draft\nstatus: draft\n---\nPrivate");
        file_put_contents($dir.'/safe.md', "---\ntitle: Safe test\nstatus: published\n---\n<script>alert(1)</script>\n[click](javascript:alert(1))");
        try {
            $pages = $this->getJson('/api/content/docs')->assertOk()->json();
            $this->assertFalse(in_array('Secret draft', array_column($pages, 'title')));
            $page = collect($pages)->firstWhere('title', 'Safe test');
            $this->assertNotNull($page);
            $this->assertStringNotContainsString('<script', $page['html']);
            $this->assertStringNotContainsString('href="javascript:', $page['html']);
            $this->get('/content/docs/security-test-fixture/draft.md')->assertNotFound();
            $this->get('/SECURITY.md')->assertNotFound();
        } finally {
            unlink($dir.'/draft.md');
            unlink($dir.'/safe.md');
            rmdir($dir);
        }
    }
}
