<?php

namespace App\Http\Controllers;

use App\Services\Atlas;
use App\Services\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    private function filters(Request $request): array
    {
        return $request->validate([
            'year' => ['sometimes', 'integer', Rule::in([1991, 2000, 2010])],
            'metric' => ['sometimes', Rule::in(Atlas::METRICS)],
            'region' => ['nullable', Rule::in(['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul'])],
            'uf' => ['nullable', 'string', Rule::exists('territories', 'uf')],
            'a' => ['nullable', 'regex:/^\d{7}$/', Rule::exists('territories', 'code')],
            'b' => ['nullable', 'regex:/^\d{7}$/', Rule::exists('territories', 'code')],
        ]);
    }

    public function bootstrap(): JsonResponse
    {
        $dataset = DB::table('datasets')->latest('id')->first();

        return response()->json([
            'territories' => DB::table('territories')->orderBy('name')->get(),
            'years' => [1991, 2000, 2010], 'observations' => DB::table('observations')->count(),
            'dataset' => $dataset ? ['name' => $dataset->name, 'sha256' => $dataset->sha256, 'imported_at' => $dataset->imported_at, 'manifest' => json_decode($dataset->manifest, true)] : null,
        ]);
    }

    public function dashboard(Request $request, Atlas $atlas): JsonResponse
    {
        return response()->json($atlas->dashboard($this->filters($request)));
    }

    public function export(Request $request, Atlas $atlas): StreamedResponse
    {
        $filters = $this->filters($request);

        return response()->streamDownload(function () use ($atlas, $filters) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['codigo_ibge', 'municipio', 'uf', 'regiao', 'ano', ...Atlas::METRICS], ',', '"', '');
            $q = $atlas->query($filters)->when($filters['year'] ?? null, fn ($q, $v) => $q->where('o.year', $v))
                ->select('t.code', 't.name', 't.uf', 't.region', 'o.year', ...array_map(fn ($m) => 'o.'.$m, Atlas::METRICS))->orderBy('t.code')->orderBy('o.year');
            foreach ($q->cursor() as $row) {
                $values = array_map(fn ($v) => is_string($v) && preg_match('/^[\s]*[=+@\-]/u', $v) ? "'".$v : $v, array_values((array) $row));
                fputcsv($out, $values, ',', '"', '');
            }
            fclose($out);
        }, 'atlas-brasil-'.($filters['year'] ?? '1991-2010').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }

    public function content(Request $request, Content $content, string $type): JsonResponse
    {
        abort_unless(in_array($type, ['docs', 'analises'], true), 404);
        $filters = $request->validate(['q' => 'nullable|string|max:120']);
        $pages = $content->pages($type);
        if ($q = $filters['q'] ?? '') {
            $needle = mb_strtolower($q);
            $pages = array_values(array_filter($pages, fn ($p) => str_contains(mb_strtolower($p['title'].' '.$p['summary'].' '.$p['text']), $needle)));
        }

        return response()->json($pages);
    }
}
