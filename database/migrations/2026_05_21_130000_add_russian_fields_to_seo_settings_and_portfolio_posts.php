<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_settings', function (Blueprint $table) {
            $table->string('meta_title_ru')->nullable()->after('keywords_en');
            $table->text('meta_description_ru')->nullable()->after('meta_title_ru');
            $table->text('keywords_ru')->nullable()->after('meta_description_ru');
        });

        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->string('title_ru')->nullable()->after('title_en');
            $table->string('category_ru', 120)->nullable()->after('category_en');
            $table->text('short_description_ru')->nullable()->after('short_description_en');
            $table->string('content_heading_ru')->nullable()->after('content_heading_en');
            $table->text('description_ru')->nullable()->after('description_en');
            $table->json('project_details_ru')->nullable()->after('project_details_en');
        });
    }

    public function down(): void
    {
        Schema::table('seo_settings', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title_ru',
                'meta_description_ru',
                'keywords_ru',
            ]);
        });

        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->dropColumn([
                'title_ru',
                'category_ru',
                'short_description_ru',
                'content_heading_ru',
                'description_ru',
                'project_details_ru',
            ]);
        });
    }
};
