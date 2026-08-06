<?php

namespace App\Services\WordPressMigration;

use App\Models\BlogPost;
use App\Models\WordPressImportItem;
use App\Models\WordPressImportRedirect;
use App\Models\WordPressImportRun;
use App\Services\BlogContentSanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class WordPressImportExecutor
{
    public function __construct(private WordPressMigrationManager $manager, private BlogContentSanitizer $sanitizer) {}

    public function import(WordPressImportRun $run, array $source): void
    {
        $mapping = WordPressImportItem::query()->where('migration_id', $run->migration_id)->where('source_type', 'post')->where('source_key', $source['source_id'])->first();
        if ($mapping?->status === 'completed') {
            if ($mapping->run_id !== $run->id) {
                $metadata = $mapping->metadata ?? [];
                $metadata['outcome'] = 'reused';
                $mapping->update(['run_id' => $run->id, 'metadata' => $metadata]);
            }
            $this->synchronizeCounts($run);

            return;
        }
        $mapping ??= WordPressImportItem::create(['run_id' => $run->id, 'migration_id' => $run->migration_id, 'source_type' => 'post', 'source_key' => $source['source_id'], 'status' => 'running', 'action' => $source['action']]);
        $mapping->update(['run_id' => $run->id, 'status' => 'running', 'error' => null]);

        try {
            $mediaMap = [];
            foreach (array_filter(array_merge($source['media_urls'], [$source['featured_image_url']])) as $url) {
                $path = $this->mediaPath($url);
                if ($path) {
                    $mediaMap[$url] = $this->copyMedia($run, $path);
                }
            }
            $successfulMedia = array_filter($mediaMap, fn ($path) => is_string($path) && $path !== '');
            $content = strtr((string) $source['content'], array_map(fn ($path) => '/storage/'.$path, $successfulMedia));
            $content = $this->sanitizer->sanitize($content);
            $thumbnail = $source['featured_image_url'] ? ($mediaMap[$source['featured_image_url']] ?? null) : null;

            DB::transaction(function () use ($run, $source, $mapping, $content, $thumbnail): void {
                if ($source['action'] === 'skip') {
                    $mapping->update(['status' => 'completed', 'metadata' => ['outcome' => 'skipped']]);

                    return;
                }
                $post = $source['action'] === 'overwrite' ? BlogPost::query()->lockForUpdate()->find($source['existing_id']) : null;
                if ($source['action'] === 'overwrite' && (! $post || ! hash_equals((string) $source['target_fingerprint'], $this->targetFingerprint($post)))) {
                    throw new RuntimeException('Esamas Blog įrašas pasikeitė po bandomojo importo. Importas sustabdytas.');
                }
                $created = ! $post;
                $post ??= new BlogPost;
                $finalSlug = $this->finalSlug($run, $source, $post);
                if ($finalSlug === null) {
                    $mapping->update(['status' => 'completed', 'metadata' => ['outcome' => 'skipped']]);

                    return;
                }
                $post->fill([
                    'title' => $source['title'], 'category' => $source['category'], 'description' => $content,
                    'project_details' => ['author' => $source['author'], 'source' => 'WordPress importas', 'show_disclaimer' => true, 'disclaimer_text' => ''],
                    'thumbnail' => $thumbnail ?? $post->thumbnail, 'position' => $post->position ?: ((int) BlogPost::max('position') + 1),
                ]);
                $post->save();
                BlogPost::query()->whereKey($post->id)->update(['slug' => $finalSlug]);
                $post->refresh();
                $mapping->update(['status' => 'completed', 'target_type' => BlogPost::class, 'target_id' => $post->id, 'metadata' => ['outcome' => $created ? 'created' : 'updated', 'owner_run_id' => $created ? $run->id : null, 'final_slug' => $finalSlug, 'imported_fingerprint' => $this->targetFingerprint($post), 'seo' => $source['seo']]]);
                if (($run->seo_settings_snapshot['redirect_handling'] ?? 'none') === 'automatic') {
                    $this->redirect($run, $source['source_url'], '/blogas/'.$finalSlug);
                }
            });
            $this->synchronizeCounts($run);
        } catch (\Throwable $exception) {
            $mapping->update(['status' => 'failed', 'error' => Str::limit($exception->getMessage(), 1000)]);
            $this->synchronizeCounts($run);
            throw $exception;
        }
    }

    private function copyMedia(WordPressImportRun $run, string $relative): ?string
    {
        $report = $this->manager->report($this->manager->find($run->migration_id));
        $findings = collect($report['uploads']['importable_media']);
        $allowed = $findings->pluck('path')->all();
        $candidate = ltrim(preg_replace('~^uploads/~', '', $relative), '/');
        $candidates = array_values(array_unique([$relative, $candidate, 'uploads/'.$candidate, 'wp-content/uploads/'.$candidate]));
        $matches = array_values(array_intersect($allowed, $candidates));
        $matched = count($matches) === 1 ? $matches[0] : null;
        if (! $matched) {
            $this->recordMissingMedia($run, $relative, 'Medijos nuoroda neatitinka vieno analizėje patvirtinto failo.');

            return null;
        }
        if (($run->settings_snapshot['image_variants'] ?? 'originals') === 'originals' && preg_match('/-\d+x\d+\.[^.]+$/i', $matched)) {
            return null;
        }

        $manifest = $this->manager->find($run->migration_id);
        $root = ($manifest['media_source']['source_type'] ?? 'zip') === 'directory' ? $manifest['media_source']['working_path'] : $this->manager->sessionPath($run->migration_id, 'extracted');
        $rootReal = realpath($root);
        $sourceReal = realpath($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $matched));
        if (! $sourceReal && str_starts_with($matched, 'uploads/')) {
            $sourceReal = realpath($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, substr($matched, 8)));
        }
        if (! $rootReal || ! $sourceReal || ! str_starts_with(strtolower($sourceReal), strtolower(rtrim($rootReal, '/\\').DIRECTORY_SEPARATOR))) {
            $this->recordMissingMedia($run, $matched, 'Analizėje patvirtintas medijos failas neberastas.');

            return null;
        }
        $checksum = hash_file('sha256', $sourceReal);
        $expectedChecksum = $findings->firstWhere('path', $matched)['sha256'] ?? null;
        if (! is_string($expectedChecksum) || strlen($expectedChecksum) !== 64 || ! hash_equals($expectedChecksum, $checksum)) {
            throw new RuntimeException('Medijos failas pasikeitė po analizės. Importas sustabdytas.');
        }
        $sourceMapping = WordPressImportItem::query()->where('migration_id', $run->migration_id)->where('source_type', 'media')->where('source_key', $matched)->first();
        if ($sourceMapping?->status === 'completed' && $sourceMapping->target_path && Storage::disk('public')->exists($sourceMapping->target_path)) {
            if ($sourceMapping->run_id !== $run->id) {
                $metadata = $sourceMapping->metadata ?? [];
                $metadata['outcome'] = 'reused';
                $sourceMapping->update(['run_id' => $run->id, 'metadata' => $metadata]);
            }

            return $sourceMapping->target_path;
        }
        $existing = WordPressImportItem::query()->where('source_type', 'media')->where('checksum', $checksum)->where('status', 'completed')->first();
        if ($existing && $existing->target_path && Storage::disk('public')->exists($existing->target_path) && ($run->settings_snapshot['duplicate_media'] ?? 'skip') === 'skip') {
            WordPressImportItem::updateOrCreate(
                ['migration_id' => $run->migration_id, 'source_type' => 'media', 'source_key' => $matched],
                ['run_id' => $run->id, 'status' => 'completed', 'action' => 'reuse', 'target_type' => 'media', 'target_path' => $existing->target_path, 'checksum' => $checksum, 'metadata' => ['outcome' => 'reused', 'owner_run_id' => $existing->metadata['owner_run_id'] ?? null]]
            );

            return $existing->target_path;
        }
        $extension = strtolower(pathinfo($sourceReal, PATHINFO_EXTENSION));
        if (! in_array($extension, config('wordpress-migration.image_extensions'), true)) {
            throw new RuntimeException('Neleistinas medijos failo tipas.');
        }
        if (@getimagesize($sourceReal) === false) {
            throw new RuntimeException('Medijos failas nėra galiojantis rastrinis paveikslėlis.');
        }
        $filename = Str::slug(pathinfo($matched, PATHINFO_FILENAME)) ?: 'image';
        $target = 'media-library/'.$filename.'-'.substr($checksum, 0, 12).'.'.$extension;
        $created = ! Storage::disk('public')->exists($target);
        if ($created) {
            $temporary = 'media-library/.wordpress-import-'.$run->id.'-'.Str::uuid().'.tmp';
            $stream = fopen($sourceReal, 'rb');
            if (! $stream) {
                throw new RuntimeException('Medijos failo nepavyko atidaryti.');
            }
            try {
                if (! Storage::disk('public')->put($temporary, $stream) || ! Storage::disk('public')->move($temporary, $target)) {
                    throw new RuntimeException('Medijos failo nepavyko saugiai įrašyti.');
                }
            } finally {
                fclose($stream);
                Storage::disk('public')->delete($temporary);
            }
        }
        try {
            WordPressImportItem::updateOrCreate(
                ['migration_id' => $run->migration_id, 'source_type' => 'media', 'source_key' => $matched],
                ['run_id' => $run->id, 'status' => 'completed', 'action' => $created ? 'copy' : 'reuse', 'target_type' => 'media', 'target_path' => $target, 'checksum' => $checksum, 'metadata' => ['outcome' => $created ? 'created' : 'reused', 'owner_run_id' => $created ? $run->id : null]]
            );
        } catch (\Throwable $exception) {
            if ($created) {
                Storage::disk('public')->delete($target);
            }
            throw $exception;
        }

        return $target;
    }

    private function mediaPath(string $url): ?string
    {
        $path = ltrim((string) parse_url(html_entity_decode($url), PHP_URL_PATH), '/');
        $at = strpos($path, 'uploads/');

        return $at === false ? null : substr($path, $at);
    }

    private function redirect(WordPressImportRun $run, string $url, string $target): void
    {
        $report = $this->manager->report($this->manager->find($run->migration_id));
        $sourceHost = strtolower((string) parse_url($url, PHP_URL_HOST));
        $siteHost = strtolower((string) parse_url($report['wordpress']['site_url'] ?? '', PHP_URL_HOST));
        if ($sourceHost === '' || $siteHost === '' || $sourceHost !== $siteHost) {
            return;
        }
        $path = '/'.ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        if ($path === '/' || $path === $target) {
            return;
        }
        $sourcePath = rtrim($path, '/') ?: '/';
        if (WordPressImportRedirect::query()->where('source_path', $target)->orWhere('target_path', $sourcePath)->exists()) {
            throw new RuntimeException('Peradresavimas sukurtų grandinę arba ciklą. Importas sustabdytas.');
        }
        $redirect = WordPressImportRedirect::query()->where('source_path', $sourcePath)->lockForUpdate()->first();
        if ($redirect && $redirect->target_path !== $target) {
            throw new RuntimeException('Peradresavimo kelias jau priklauso kitam tikslui. Importas sustabdytas.');
        }
        $redirect ??= WordPressImportRedirect::create(['source_path' => $sourcePath, 'run_id' => $run->id, 'target_path' => $target]);
        if ($redirect->wasRecentlyCreated) {
            // Counts are projected from persisted redirects after each item.
        }
    }

    public function synchronizeCounts(WordPressImportRun $run): void
    {
        $items = WordPressImportItem::query()->where('run_id', $run->id)->get(['source_type', 'status', 'metadata']);
        $posts = $items->where('source_type', 'post');
        $media = $items->where('source_type', 'media');
        $outcome = fn ($item) => $item->metadata['outcome'] ?? null;
        WordPressImportRun::query()->whereKey($run->id)->update([
            'processed_items' => $posts->where('status', 'completed')->count(),
            'created_count' => $posts->filter(fn ($item) => $outcome($item) === 'created')->count(),
            'updated_count' => $posts->filter(fn ($item) => $outcome($item) === 'updated')->count(),
            'skipped_count' => $posts->filter(fn ($item) => in_array($outcome($item), ['skipped', 'reused'], true))->count(),
            'failed_count' => $posts->where('status', 'failed')->count(),
            'media_copied' => $media->filter(fn ($item) => $outcome($item) === 'created')->count(),
            'media_reused' => $media->filter(fn ($item) => $outcome($item) === 'reused')->count(),
            'media_failed' => $media->where('status', 'failed')->count(),
            'redirects_created' => WordPressImportRedirect::query()->where('run_id', $run->id)->count(),
        ]);
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

    private function finalSlug(WordPressImportRun $run, array $source, BlogPost $post): ?string
    {
        $slug = $source['final_slug'];
        $conflict = BlogPost::query()->where('slug', $slug)->when($post->exists, fn ($query) => $query->whereKeyNot($post->id))->lockForUpdate()->exists();
        if (! $conflict) {
            return $slug;
        }
        $strategy = $run->seo_settings_snapshot['slug_conflicts'] ?? 'skip';
        if ($strategy === 'skip') {
            return null;
        }
        if ($strategy !== 'generate') {
            throw new RuntimeException('Po bandomojo importo atsirado naujas slug konfliktas. Importas sustabdytas.');
        }
        $suffix = 1;
        do {
            $suffixText = '-'.$suffix++;
            $slug = Str::substr($source['final_slug'], 0, 255 - strlen($suffixText)).$suffixText;
        } while (BlogPost::query()->where('slug', $slug)->lockForUpdate()->exists());

        return $slug;
    }

    private function recordMissingMedia(WordPressImportRun $run, string $sourceKey, string $message): void
    {
        WordPressImportItem::updateOrCreate(
            ['migration_id' => $run->migration_id, 'source_type' => 'media', 'source_key' => $sourceKey],
            ['run_id' => $run->id, 'status' => 'failed', 'action' => 'missing', 'target_type' => 'media', 'error' => $message, 'metadata' => ['outcome' => 'missing', 'owner_run_id' => null]]
        );
    }
}
