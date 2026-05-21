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
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique();
            $table->string('meta_title_lt')->nullable();
            $table->text('meta_description_lt')->nullable();
            $table->text('keywords_lt')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('keywords_en')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};
