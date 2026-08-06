<?php

namespace App\Services\WordPressMigration;

use RuntimeException;

class WordPressSqlAnalyzer
{
    private const SEO = ['Yoast SEO' => ['_yoast_wpseo_'], 'Rank Math' => ['rank_math_'], 'All in One SEO' => ['_aioseo_', '_aioseop_'], 'The SEO Framework' => ['_genesis_', '_tsf_', '_social_image_url']];

    public function analyze(string $path): array
    {
        $gzip = str_ends_with(strtolower($path), '.gz');
        $handle = $gzip ? @gzopen($path, 'rb') : @fopen($path, 'rb');
        if (! $handle) throw new RuntimeException('Nepavyko atidaryti SQL failo.');
        $result = ['table_prefix' => null, 'tables' => [], 'post_records' => 0, 'postmeta_keys' => [], 'user_records' => 0, 'seo' => ['detected_systems' => [], 'keys' => [], 'records_found' => 0], 'redirect_keys' => [], 'view_count_keys' => [], 'unknown_plugin_keys' => []];
        try {
            while (($line = $gzip ? gzgets($handle) : fgets($handle)) !== false) {
                if (preg_match_all('/(?:CREATE TABLE|INSERT INTO)\s+[`\"]?([A-Za-z0-9_]+)[`\"]?/i', $line, $matches)) foreach ($matches[1] as $table) { $result['tables'][$table] = true; if (! $result['table_prefix'] && preg_match('/^(.+)_posts$/', $table, $m)) $result['table_prefix'] = $m[1].'_'; }
                $prefix = preg_quote($result['table_prefix'] ?? 'wp_', '/');
                if (preg_match("/INSERT INTO [`\"]?{$prefix}posts/i", $line)) $result['post_records'] += substr_count($line, '),(') + 1;
                if (preg_match("/INSERT INTO [`\"]?{$prefix}users/i", $line)) $result['user_records'] += substr_count($line, '),(') + 1;
                if (preg_match("/INSERT INTO [`\"]?{$prefix}postmeta/i", $line)) {
                    preg_match_all("/'((?:_|[A-Za-z])[A-Za-z0-9_.:-]{2,100})'/", $line, $keys);
                    foreach ($keys[1] as $key) $result['postmeta_keys'][$key] = ($result['postmeta_keys'][$key] ?? 0) + 1;
                }
            }
        } finally { $gzip ? gzclose($handle) : fclose($handle); }
        $result['tables'] = array_keys($result['tables']);
        foreach ($result['postmeta_keys'] as $key => $count) {
            $known = false;
            foreach (self::SEO as $system => $patterns) foreach ($patterns as $pattern) if (str_contains($key, $pattern)) { $result['seo']['detected_systems'][$system] = true; $result['seo']['keys'][$key] = $count; $result['seo']['records_found'] += $count; $known = true; }
            if (str_contains($key, 'redirect')) $result['redirect_keys'][$key] = $count;
            elseif (preg_match('/view|count/i', $key)) $result['view_count_keys'][$key] = $count;
            elseif (! $known && str_starts_with($key, '_')) $result['unknown_plugin_keys'][$key] = $count;
        }
        $result['seo']['detected_systems'] = array_keys($result['seo']['detected_systems']);
        $result['relevant_tables'] = array_values(array_filter($result['tables'], fn ($t) => preg_match('/_(posts|postmeta|terms|term_taxonomy|term_relationships|users|usermeta)$/', $t)));
        return $result;
    }
}
