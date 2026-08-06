<?php

namespace App\Services\WordPressMigration;

use App\Models\BlogPost;
use App\Services\BlogContentSanitizer;
use App\Support\BlogCategories;
use Illuminate\Support\Str;
use RuntimeException;

class WordPressImportPlanService
{
    public function __construct(
        private WordPressMigrationManager $manager,
        private WordPressImportSourceReader $reader,
        private BlogContentSanitizer $sanitizer,
    ) {}

    public function build(string $migrationId): array
    {
        [$manifest, $report] = $this->loadPrerequisites($migrationId);

        $settings = $manifest['import_settings'];
        $seo = $manifest['seo_settings'];
        $blocking = [];
        if (! empty($report['uploads']['high_risk_executable_files'])) {
            $blocking[] = 'Aptikti didelės rizikos vykdomieji failai. Importavimas negalimas.';
        }
        if (collect($report['uploads']['importable_media'])->contains(fn (array $finding) => empty($finding['sha256']))) {
            $blocking[] = 'Medijos analizė neturi importui privalomų kontrolinių sumų. Pakartokite šaltinių analizę.';
        }
        if (! empty($settings['tags'])) {
            $blocking[] = 'Dabartinis Blog modelis nepalaiko žymų importavimo. Išjunkite žymų importą.';
        }
        if (! empty($settings['comments'])) {
            $blocking[] = 'Dabartinis Blog modelis nepalaiko komentarų importavimo. Išjunkite komentarų importą.';
        }
        if (! empty($settings['preserve_upload_structure'])) {
            $blocking[] = 'Bendra Medijos biblioteka nepalaiko WordPress katalogų struktūros. Išjunkite struktūros išsaugojimą.';
        }
        if (($settings['image_variants'] ?? 'originals') !== 'originals') {
            $blocking[] = 'Sugeneruotų WordPress variantų importas nepalaikomas. Pasirinkite tik originalus.';
        }
        if (($settings['duplicate_media'] ?? 'skip') !== 'skip') {
            $blocking[] = 'Atskiros medijos kopijos nepalaikomos saugiai. Pasirinkite esamų kopijų panaudojimą.';
        }
        if (! empty($settings['seo']) && (! empty($seo['meta_title']) || ! empty($seo['meta_description']) || ! empty($seo['canonical_url']))) {
            $blocking[] = 'Dabartinis Blog modelis neturi straipsnio SEO laukų. Išjunkite nepalaikomą SEO importą.';
        }
        $xmlPath = $this->manager->sessionPath($migrationId, 'source/'.$manifest['files']['wordpress_xml']['stored_name']);
        $attachments = [];
        foreach ($this->reader->posts($xmlPath) as $item) {
            if ($item['type'] === 'attachment') {
                $attachments[$item['id']] = $item;
            }
        }

        $items = [];
        $warnings = $report['warnings'] ?? [];
        $skipped = [];
        $seenIds = [];
        $usedSlugs = [];
        foreach ($this->reader->posts($xmlPath) as $source) {
            if ($source['type'] === 'attachment') {
                continue;
            }
            if ($source['type'] !== 'post' || $source['status'] !== 'publish' || empty($settings['posts'])) {
                $skipped[] = ['id' => $source['id'], 'title' => $source['title'], 'reason' => $source['type'] !== 'post' ? 'Nepalaikomas turinio tipas.' : ($source['status'] !== 'publish' ? 'Straipsnis nėra paskelbtas.' : 'Straipsnių importas išjungtas.')];

                continue;
            }
            if ($source['id'] === '' || isset($seenIds[$source['id']])) {
                $blocking[] = 'Aptiktas pasikartojantis arba tuščias WordPress įrašo ID.';

                continue;
            }
            $seenIds[$source['id']] = true;
            if ($source['title'] === '') {
                $skipped[] = ['id' => $source['id'], 'title' => '', 'reason' => 'Trūksta privalomo pavadinimo.'];

                continue;
            }

            $baseSlug = Str::slug($source['slug'] ?: $source['title']) ?: 'blog-post';
            $existing = BlogPost::query()->where('slug', $baseSlug)->first();
            $action = $existing ? ($seo['slug_conflicts'] ?? 'skip') : 'create';
            $finalSlug = $baseSlug;
            if (($existing || isset($usedSlugs[$finalSlug])) && $action === 'generate') {
                $finalSlug = $this->availableSlug($baseSlug, $usedSlugs);
            }
            if (isset($usedSlugs[$finalSlug]) && $action !== 'generate') {
                $action = 'skip';
            }
            $usedSlugs[$finalSlug] = true;

            $raw = $this->normalizeAlignment($source['content']);
            $content = $this->sanitizer->sanitize($raw);
            if ($content !== trim($raw)) {
                $warnings[] = 'Kai kurių straipsnių HTML buvo saugiai išvalytas.';
            }
            preg_match_all('/\[(?:\/?)[a-z][^\]]*\]/i', $source['content'], $shortcodes);
            preg_match_all('/(?:src|href)=["\']([^"\']+)["\']/i', $source['content'], $links);
            $media = ! empty($settings['images']) ? array_values(array_unique(array_filter($links[1] ?? [], fn ($url) => preg_match('/\.(?:jpe?g|png|gif|webp|avif)(?:\?|$)/i', $url)))) : [];
            $featured = ! empty($settings['featured_images']) && $source['featured_image_id'] ? ($attachments[$source['featured_image_id']]['attachment_url'] ?? null) : null;
            if ($source['featured_image_id'] && ! $featured) {
                $warnings[] = 'Ne visos miniatiūros buvo išspręstos.';
            }

            $items[] = [
                'source_id' => $source['id'], 'title' => $source['title'], 'source_slug' => $baseSlug,
                'final_slug' => $finalSlug, 'source_url' => $source['url'], 'target_url' => '/blogas/'.$finalSlug,
                'action' => $action, 'existing_id' => $existing?->id, 'target_fingerprint' => $existing ? $this->targetFingerprint($existing) : null, 'author' => ! empty($settings['authors']) ? ($source['author'] ?: 'Tūpk Stok redakcija') : 'Tūpk Stok redakcija',
                'category' => ! empty($settings['categories']) ? BlogCategories::normalize($source['categories'][0] ?? null) : BlogCategories::DEFAULT, 'categories' => $source['categories'],
                'content' => $content, 'featured_image_url' => $featured, 'media_urls' => $media,
                'comment_count' => $source['comment_count'], 'shortcodes' => array_values(array_unique($shortcodes[0] ?? [])),
                'seo' => $this->seo($source['meta'], $seo),
            ];
        }

        $fingerprints = $this->manager->currentSourceFingerprints($migrationId);
        $payload = [
            'plan_version' => 1, 'migration_id' => $migrationId, 'source_fingerprints' => $fingerprints,
            'settings' => $settings, 'seo_settings' => $seo, 'items' => $items, 'skipped' => $skipped,
            'warnings' => array_values(array_unique($warnings)), 'blocking_errors' => array_values(array_unique($blocking)),
            'forecast' => [
                'create' => count(array_filter($items, fn ($i) => $i['action'] === 'create')),
                'update' => count(array_filter($items, fn ($i) => $i['action'] === 'overwrite')),
                'skip' => count($skipped) + count(array_filter($items, fn ($i) => $i['action'] === 'skip')),
                'media_references' => count(array_unique($items ? array_merge(...array_map(fn ($i) => array_filter(array_merge($i['media_urls'], [$i['featured_image_url']])), $items)) : [])),
                'redirects' => ($seo['redirect_handling'] ?? 'none') === 'automatic' ? count(array_filter($items, fn ($i) => $i['source_url'] !== '')) : 0,
                'chunks' => (int) ceil(max(1, count($items)) / 10),
            ],
        ];
        $payload['fingerprint'] = hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $payload['generated_at'] = now()->toIso8601String();
        $this->manager->storePrivateJson($migrationId, 'reports/dry-run.json', $payload);
        $this->manager->updateManifest($migrationId, ['dry_run_status' => 'complete', 'dry_run_path' => 'reports/dry-run.json', 'dry_run_fingerprint' => $payload['fingerprint'], 'dry_run_created_at' => $payload['generated_at'], 'dry_run_error' => null]);

        return $payload;
    }

    public function loadPrerequisites(string $migrationId): array
    {
        $manifest = $this->manager->find($migrationId);
        $report = $this->manager->report($manifest);
        if (! $report || ($manifest['analysis_status'] ?? null) !== 'complete') {
            throw new RuntimeException('Pirmiausia reikia užbaigti šaltinių analizę.');
        }
        if (empty($manifest['import_settings_saved_at']) || empty($manifest['seo_settings_saved_at'])) {
            throw new RuntimeException('Importavimo ir SEO nustatymai nėra išsaugoti.');
        }

        return [$manifest, $report];
    }

    public function loadAndValidate(string $migrationId, bool $verifyLiveHashes = true): array
    {
        $manifest = $this->manager->find($migrationId);
        if (empty($manifest['dry_run_path'])) {
            throw new RuntimeException('Bandomojo importo planas nerastas.');
        }
        $plan = $this->manager->readPrivateJson($migrationId, $manifest['dry_run_path']);
        $fingerprintPayload = $plan;
        unset($fingerprintPayload['fingerprint'], $fingerprintPayload['generated_at']);
        $computedFingerprint = hash('sha256', json_encode($fingerprintPayload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        if (! isset($plan['fingerprint']) || ! hash_equals($computedFingerprint, (string) $plan['fingerprint']) || ! hash_equals((string) ($manifest['dry_run_fingerprint'] ?? ''), (string) $plan['fingerprint'])) {
            throw new RuntimeException('Bandomojo importo planas pasikeitė arba yra sugadintas. Pakartokite bandomąjį importą.');
        }
        if (! $this->manager->sourceMetadataIsCurrent($migrationId)) {
            throw new RuntimeException('Šaltinio failai pasikeitė. Pakartokite bandomąjį importą.');
        }
        if ($verifyLiveHashes && ($plan['source_fingerprints'] ?? []) !== $this->manager->currentSourceFingerprints($migrationId)) {
            throw new RuntimeException('Šaltinio failai pasikeitė. Pakartokite bandomąjį importą.');
        }
        if (($plan['settings'] ?? []) !== ($manifest['import_settings'] ?? []) || ($plan['seo_settings'] ?? []) !== ($manifest['seo_settings'] ?? [])) {
            throw new RuntimeException('Nustatymai pasikeitė. Pakartokite bandomąjį importą.');
        }
        if (! empty($plan['blocking_errors'])) {
            throw new RuntimeException('Bandomajame importe yra blokuojančių klaidų.');
        }

        return $plan;
    }

    private function availableSlug(string $base, array $used): string
    {
        $suffix = 1;
        $slug = $base;
        while (isset($used[$slug]) || BlogPost::query()->where('slug', $slug)->exists()) {
            $slug = Str::limit($base, 245, '').'-'.$suffix++;
        }

        return $slug;
    }

    private function targetFingerprint(BlogPost $post): string
    {
        return hash('sha256', json_encode([
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'category' => $post->category,
            'description' => $post->description,
            'project_details' => $post->project_details,
            'thumbnail' => $post->thumbnail,
            'youtube_url' => $post->youtube_url,
            'position' => $post->position,
            'updated_at' => $post->updated_at?->toJSON(),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function normalizeAlignment(string $html): string
    {
        return str_replace(['alignleft', 'aligncenter', 'alignright'], ['article-image-left', 'article-image-center', 'article-image-right'], $html);
    }

    private function seo(array $meta, array $settings): array
    {
        $values = ['title' => null, 'description' => null, 'canonical' => null];
        $keys = [
            'title' => ['_yoast_wpseo_title', 'rank_math_title', '_aioseo_title'],
            'description' => ['_yoast_wpseo_metadesc', 'rank_math_description', '_aioseo_description'],
            'canonical' => ['_yoast_wpseo_canonical', 'rank_math_canonical_url', '_aioseo_canonical_url'],
        ];
        foreach ($keys as $field => $candidates) {
            foreach ($candidates as $key) {
                if (($meta[$key] ?? '') !== '') {
                    $values[$field] = $meta[$key];
                    break;
                }
            }
        }
        if (empty($settings['meta_title'])) {
            $values['title'] = null;
        }
        if (empty($settings['meta_description'])) {
            $values['description'] = null;
        }
        if (empty($settings['canonical_url'])) {
            $values['canonical'] = null;
        }

        return $values;
    }
}
