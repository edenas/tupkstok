<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wordpress_import_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('migration_id')->index();
            $table->foreignId('administrator_id')->constrained('users');
            $table->string('source_type', 32);
            $table->string('status', 32)->index();
            $table->string('stage', 64)->nullable();
            $table->string('lock_owner')->nullable();
            $table->boolean('is_test')->default(false);
            $table->unsignedInteger('test_limit')->nullable();
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('processed_items')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('media_copied')->default(0);
            $table->unsignedInteger('media_reused')->default(0);
            $table->unsignedInteger('media_failed')->default(0);
            $table->unsignedInteger('redirects_created')->default(0);
            $table->json('settings_snapshot');
            $table->json('seo_settings_snapshot');
            $table->json('source_fingerprints');
            $table->string('dry_run_fingerprint', 64);
            $table->string('import_fingerprint', 64)->unique();
            $table->string('plan_path');
            $table->string('report_path')->nullable();
            $table->json('warnings')->nullable();
            $table->json('errors')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wordpress_import_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('run_id')->index();
            $table->uuid('migration_id');
            $table->string('source_type', 32);
            $table->string('source_key');
            $table->string('status', 32)->index();
            $table->string('action', 32)->nullable();
            $table->string('target_type', 64)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_path')->nullable();
            $table->string('checksum', 64)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->unique(['migration_id', 'source_type', 'source_key'], 'wordpress_import_source_unique');
        });

        Schema::create('wordpress_import_redirects', function (Blueprint $table) {
            $table->id();
            $table->uuid('run_id')->index();
            $table->string('source_path')->unique();
            $table->string('target_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wordpress_import_redirects');
        Schema::dropIfExists('wordpress_import_items');
        Schema::dropIfExists('wordpress_import_runs');
    }
};
