<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->string('category_en', 120)->nullable()->after('category');
            $table->text('short_description_en')->nullable()->after('short_description');
            $table->string('content_heading_en')->nullable()->after('content_heading');
            $table->text('description_en')->nullable()->after('description');
            $table->json('project_details_en')->nullable()->after('project_details');
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->dropColumn([
                'title_en',
                'category_en',
                'short_description_en',
                'content_heading_en',
                'description_en',
                'project_details_en',
            ]);
        });
    }
};
