<?php

namespace App\Jobs;

use App\Services\WordPressMigration\WordPressImportPlanService;
use App\Services\WordPressMigration\WordPressMigrationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateWordPressImportDryRun implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(public string $migrationId) {}

    public function handle(WordPressImportPlanService $plans, WordPressMigrationManager $manager): void
    {
        $manager->updateManifest($this->migrationId, ['dry_run_status' => 'running', 'dry_run_started_at' => now()->toIso8601String()]);
        $plans->build($this->migrationId);
    }

    public function failed(\Throwable $exception): void
    {
        app(WordPressMigrationManager::class)->updateManifest($this->migrationId, ['dry_run_status' => 'failed', 'dry_run_error' => 'Bandomojo importo parengti nepavyko.']);
        report($exception);
    }
}
