<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        $usedSlugs = [];

        foreach (DB::table('portfolio_posts')
            ->orderBy('id')
            ->select(['id', 'title'])
            ->cursor() as $portfolioPost) {
            $baseSlug = Str::slug((string) $portfolioPost->title);
            $baseSlug = $baseSlug !== '' ? $baseSlug : 'portfolio-post';
            $baseSlug = Str::substr($baseSlug, 0, 255);
            $slug = $baseSlug;
            $suffix = 1;

            while (isset($usedSlugs[$slug])) {
                $suffixText = '-'.$suffix;
                $slug = Str::substr($baseSlug, 0, 255 - strlen($suffixText)).$suffixText;
                $suffix++;
            }

            $usedSlugs[$slug] = true;

            DB::table('portfolio_posts')
                ->where('id', $portfolioPost->id)
                ->update(['slug' => $slug]);
        }

        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
