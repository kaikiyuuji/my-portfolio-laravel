<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('location')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('linkedin_url', 2048)->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->json('education')->nullable();
            $table->json('certifications')->nullable();
            $table->json('languages')->nullable();
            $table->json('interests')->nullable();
            $table->timestamps();
        });
        Schema::table('experiences', function (Blueprint $table) {
            $table->string('location')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn('location');
        });
        Schema::dropIfExists('resume_settings');
    }
};
