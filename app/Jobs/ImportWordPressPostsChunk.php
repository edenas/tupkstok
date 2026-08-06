<?php

namespace App\Jobs;

use App\Models\WordPressImportRun;
use App\Services\WordPressMigration\WordPressImportExecutor;
use App\Services\WordPressMigration\WordPressImportLock;
use App\Services\WordPressMigration\WordPressMigrationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportWordPressPostsChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 240;

    public function __construct(public string $runId, public int $offset, public int $limit) {}

    public function handle(WordPressImportExecutor $executor, WordPressMigrationManager $manager, WordPressImportLock $lock): void
    {
        $run = WordPressImportRun::query()->findOrFail($this->runId);
        if (! $lock->acquire($run)) {
            $this->release(30);

            return;
        }
        $plan = $manager->readPrivateJson($run->migration_id, $run->plan_path);
        foreach (array_slice($plan['items'], $this->offset, $this->limit) as $item) {
            $executor->import($run, $item);
            $run->update(['stage' => 'Importuojamas straipsnis: '.mb_substr($item['title'], 0, 80), 'last_activity_at' => now()]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        WordPressImportRun::query()->whereKey($this->runId)->update(['status' => 'failed', 'stage' => 'Straipsnių importavimas nepavyko', 'errors' => [$exception->getMessage()], 'last_activity_at' => now()]);
        $run = WordPressImportRun::query()->find($this->runId);
        if ($run) {
            app(WordPressImportLock::class)->release($run);
        }
        report($exception);
    }
}
