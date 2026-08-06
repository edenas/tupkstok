<?php

namespace App\Jobs;

use App\Models\WordPressImportRun;
use App\Services\WordPressMigration\WordPressImportLock;
use App\Services\WordPressMigration\WordPressImportPlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class StartWordPressImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public string $runId) {}

    public function middleware(): array
    {
        return [(new WithoutOverlapping('wordpress-import:'.$this->runId))->expireAfter(600)];
    }

    public function handle(WordPressImportPlanService $plans, WordPressImportLock $lock): void
    {
        $run = WordPressImportRun::query()->findOrFail($this->runId);
        if (in_array($run->status, ['running', 'completed', 'completed_with_warnings'], true)) {
            return;
        }
        if (! $lock->acquire($run)) {
            $this->release(30);

            return;
        }
        $plan = $plans->loadAndValidate($run->migration_id);
        if (! hash_equals($run->dry_run_fingerprint, (string) $plan['fingerprint'])) {
            throw new \RuntimeException('Patvirtintas bandomojo importo planas buvo pakeistas. Importas sustabdytas.');
        }
        $total = $run->is_test ? min(count($plan['items']), (int) $run->test_limit) : count($plan['items']);
        $run->update(['status' => 'running', 'stage' => 'Straipsnių importavimas', 'total_items' => $total, 'started_at' => $run->started_at ?? now(), 'last_activity_at' => now()]);
        $jobs = [];
        for ($offset = 0; $offset < $total; $offset += 10) {
            $jobs[] = new ImportWordPressPostsChunk($run->id, $offset, min(10, $total - $offset));
        }
        $jobs[] = new FinalizeWordPressImport($run->id);
        Bus::chain($jobs)->dispatch();
    }

    public function failed(\Throwable $exception): void
    {
        $run = WordPressImportRun::query()->find($this->runId);
        if ($run) {
            $run->update(['status' => 'failed', 'stage' => 'Nepavyko pradėti importavimo', 'errors' => [$exception->getMessage()], 'last_activity_at' => now()]);
            app(WordPressImportLock::class)->release($run);
        }
        report($exception);
    }
}
