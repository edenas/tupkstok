<?php

namespace App\Services\WordPressMigration;

use App\Models\WordPressImportRun;
use Illuminate\Support\Facades\Cache;

class WordPressImportLock
{
    private const KEY = 'wordpress-import:production-lifecycle';

    private const SECONDS = 21600;

    public function acquire(WordPressImportRun $run): bool
    {
        if ($run->lock_owner) {
            return Cache::restoreLock(self::KEY, $run->lock_owner)->get();
        }
        $lock = Cache::lock(self::KEY, self::SECONDS);
        if (! $lock->get()) {
            return false;
        }
        $run->update(['lock_owner' => $lock->owner()]);

        return true;
    }

    public function release(WordPressImportRun $run): void
    {
        if (! $run->lock_owner) {
            return;
        }
        Cache::restoreLock(self::KEY, $run->lock_owner)->release();
        $run->update(['lock_owner' => null]);
    }
}
