<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seo_settings')) {
            return;
        }

        Schema::table('seo_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('seo_settings', 'meta_title_ru')) {
                $table->string('meta_title_ru')->nullable();
            }

            if (! Schema::hasColumn('seo_settings', 'meta_description_ru')) {
                $table->text('meta_description_ru')->nullable();
            }

            if (! Schema::hasColumn('seo_settings', 'keywords_ru')) {
                $table->text('keywords_ru')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('seo_settings')) {
            return;
        }

        $columns = array_values(array_filter([
            Schema::hasColumn('seo_settings', 'meta_title_ru') ? 'meta_title_ru' : null,
            Schema::hasColumn('seo_settings', 'meta_description_ru') ? 'meta_description_ru' : null,
            Schema::hasColumn('seo_settings', 'keywords_ru') ? 'keywords_ru' : null,
        ]));

        if ($columns === []) {
            return;
        }

        Schema::table('seo_settings', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
