<?php

namespace App\Services;

use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

final class Content
{
    public function pages(string $type): array
    {
        $pattern = $type === 'docs' ? base_path('content/docs/*/*.md') : base_path('content/analises/*.md');
        $pages = [];
        foreach (glob($pattern) ?: [] as $file) {
            $raw = file_get_contents($file);
            if (! preg_match('/\A---\R(.*?)\R---\R(.*)\z/s', $raw, $parts)) {
                continue;
            }
            $meta = Yaml::parse($parts[1]);
            if (! is_array($meta) || ($meta['status'] ?? '') !== 'published') {
                continue;
            }
            $slug = basename($file, '.md');
            $category = $type === 'docs' ? basename(dirname($file)) : 'analises';
            $pages[] = [
                'slug' => $slug, 'category' => $category, 'title' => (string) ($meta['title'] ?? $slug),
                'summary' => (string) ($meta['summary'] ?? ''), 'order' => (int) ($meta['order'] ?? 99),
                'updated_at' => is_int($meta['updated_at'] ?? null) ? gmdate('Y-m-d', $meta['updated_at']) : (string) ($meta['updated_at'] ?? '2026-11-18'),
                'author' => (string) ($meta['author'] ?? 'Equipe do projeto'), 'chart' => $meta['chart'] ?? null,
                'dataset_sha256' => $meta['dataset_sha256'] ?? null,
                'url' => $type === 'docs' ? "/docs/{$category}/{$slug}" : "/analises/{$slug}",
                'html' => Str::markdown($parts[2], ['html_input' => 'strip', 'allow_unsafe_links' => false]),
                'text' => strip_tags(Str::markdown($parts[2], ['html_input' => 'strip', 'allow_unsafe_links' => false])),
            ];
        }
        usort($pages, fn ($a, $b) => ($a['order'] <=> $b['order']) ?: strcmp($a['title'], $b['title']));

        return $pages;
    }
}
