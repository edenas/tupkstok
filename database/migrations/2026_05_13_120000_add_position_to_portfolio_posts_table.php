<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->integer('position')->nullable()->after('youtube_url');
        });

        DB::table('portfolio_posts')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['id'])
            ->each(function ($post, int $index): void {
                DB::table('portfolio_posts')
                    ->where('id', $post->id)
                    ->update(['position' => $index + 1]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
