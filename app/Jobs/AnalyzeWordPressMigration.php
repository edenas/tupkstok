<?php

namespace App\Jobs;

use App\Services\WordPressMigration\WordPressMigrationManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalyzeWordPressMigration implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $importId) {}

    public function handle(WordPressMigrationManager $manager): void
    {
        $manager->analyze($this->importId);
    }
}
