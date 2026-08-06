<?php

namespace App\Jobs;

use App\Models\WordPressImportItem;
use App\Models\WordPressImportRun;
use App\Services\WordPressMigration\WordPressImportExecutor;
use App\Services\WordPressMigration\WordPressImportLock;
use App\Services\WordPressMigration\WordPressMigrationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FinalizeWordPressImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public string $runId) {}

    public function handle(WordPressMigrationManager $manager, WordPressImportExecutor $executor, WordPressImportLock $lock): void
    {
        $run = WordPressImportRun::query()->findOrFail($this->runId);
        if (! $lock->acquire($run)) {
            $this->release(30);

            return;
        }
        $executor->synchronizeCounts($run);
        $run->refresh();
        $items = WordPressImportItem::query()->where('run_id', $run->id)->get();
        $report = [
            'report_version' => 1, 'run_id' => $run->id, 'migration_id' => $run->migration_id, 'test_import' => $run->is_test,
            'counts' => $run->only(['total_items', 'processed_items', 'created_count', 'updated_count', 'skipped_count', 'failed_count', 'media_copied', 'media_reused', 'media_failed', 'redirects_created']),
            'posts' => $items->where('source_type', 'post')->map->only(['source_key', 'status', 'action', 'target_id', 'metadata', 'error'])->values()->all(),
            'media' => $items->where('source_type', 'media')->map->only(['source_key', 'status', 'target_path', 'checksum', 'metadata', 'error'])->values()->all(),
            'warnings' => $run->warnings ?? [], 'errors' => $run->errors ?? [], 'source_fingerprints' => $run->source_fingerprints,
            'settings' => $run->settings_snapshot, 'seo_settings' => $run->seo_settings_snapshot, 'completed_at' => now()->toIso8601String(),
        ];
        $path = 'reports/import-'.$run->id.'.json';
        $manager->storePrivateJson($run->migration_id, $path, $report);
        $status = $run->failed_count > 0 ? 'completed_with_warnings' : 'completed';
        $run->update(['status' => $status, 'stage' => 'Baigta', 'report_path' => $path, 'completed_at' => now(), 'last_activity_at' => now()]);
        $lock->release($run);
    }

    public function failed(\Throwable $exception): void
    {
        $run = WordPressImportRun::query()->find($this->runId);
        if ($run) {
            $run->update(['status' => 'failed', 'stage' => 'Galutinės ataskaitos parengimas nepavyko', 'errors' => [$exception->getMessage()], 'last_activity_at' => now()]);
            app(WordPressImportLock::class)->release($run);
        }
        report($exception);
    }
}
