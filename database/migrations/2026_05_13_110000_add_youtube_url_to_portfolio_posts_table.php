<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->after('post_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->dropColumn('youtube_url');
        });
    }
};
