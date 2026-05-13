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
            $table->text('short_description')->nullable()->after('category');
            $table->string('content_heading')->nullable()->after('short_description');
            $table->json('project_details')->nullable()->after('description');
        });

        DB::table('portfolio_posts')->update([
            'short_description' => DB::raw('description'),
        ]);

        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->longText('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->dropColumn(['short_description', 'content_heading', 'project_details']);
        });
    }
};
