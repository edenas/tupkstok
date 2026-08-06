<?php

namespace App\Jobs;

use App\Models\BlogPost;
use App\Models\WordPressImportItem;
use App\Models\WordPressImportRedirect;
use App\Models\WordPressImportRun;
use App\Services\WordPressMigration\WordPressImportLock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RollbackWordPressImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(public string $runId) {}

    public function handle(WordPressImportLock $lock): void
    {
        $run = WordPressImportRun::query()->findOrFail($this->runId);
        if ($run->status !== 'rolling_back') {
            return;
        }
        if (! $lock->acquire($run)) {
            $this->release(30);

            return;
        }
        $run->update(['stage' => 'Saugiai atšaukiami sukurti įrašai', 'last_activity_at' => now()]);
        $created = WordPressImportItem::query()->where('source_type', 'post')->where('metadata->owner_run_id', $run->id)->get();
        DB::transaction(function () use ($created, $run): void {
            foreach ($created as $item) {
                if ($item->target_id) {
                    $post = BlogPost::query()->whereKey($item->target_id)->lockForUpdate()->first();
                    if ($post && hash_equals((string) ($item->metadata['imported_fingerprint'] ?? ''), $this->targetFingerprint($post))) {
                        $post->delete();
                        $item->update(['status' => 'rolled_back']);
                    }
                }
            }
            WordPressImportRedirect::query()->where('run_id', $run->id)->delete();
        });
        $media = WordPressImportItem::query()->where('source_type', 'media')->where('metadata->owner_run_id', $run->id)->get();
        foreach ($media as $item) {
            if (! $item->target_path) {
                continue;
            }
            $referenced = BlogPost::query()->where('thumbnail', $item->target_path)->orWhere('description', 'like', '%'.addcslashes($item->target_path, '%_\\').'%')->exists();
            if (! $referenced) {
                if (Storage::disk('public')->delete($item->target_path) || ! Storage::disk('public')->exists($item->target_path)) {
                    $item->update(['status' => 'rolled_back']);
                }
            }
        }
        $run->update(['status' => 'rolled_back', 'stage' => 'Saugiai atšaukta', 'import_fingerprint' => hash('sha256', $run->import_fingerprint.'|rolled-back|'.now()->toJSON()), 'last_activity_at' => now()]);
        $lock->release($run);
    }

    public function failed(\Throwable $exception): void
    {
        $run = WordPressImportRun::query()->find($this->runId);
        if ($run) {
            $run->update(['status' => 'rollback_failed', 'stage' => 'Saugus atšaukimas nepavyko', 'errors' => [$exception->getMessage()], 'last_activity_at' => now()]);
            app(WordPressImportLock::class)->release($run);
        }
        report($exception);
    }

    private function targetFingerprint(BlogPost $post): string
    {
        return hash('sha256', json_encode(['id' => $post->id, 'title' => $post->title, 'slug' => $post->slug, 'category' => $post->category, 'description' => $post->description, 'project_details' => $post->project_details, 'thumbnail' => $post->thumbnail, 'youtube_url' => $post->youtube_url, 'position' => $post->position, 'updated_at' => $post->updated_at?->toJSON()], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
