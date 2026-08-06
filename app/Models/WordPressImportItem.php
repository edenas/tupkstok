<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WordPressImportItem extends Model
{
    protected $table = 'wordpress_import_items';

    protected $guarded = [];

    protected $casts = ['metadata' => 'array'];
}
