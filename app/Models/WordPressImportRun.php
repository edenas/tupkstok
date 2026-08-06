<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WordPressImportRun extends Model
{
    protected $table = 'wordpress_import_runs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = ['is_test' => 'boolean', 'settings_snapshot' => 'array', 'seo_settings_snapshot' => 'array', 'source_fingerprints' => 'array', 'warnings' => 'array', 'errors' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime', 'last_activity_at' => 'datetime'];
}
