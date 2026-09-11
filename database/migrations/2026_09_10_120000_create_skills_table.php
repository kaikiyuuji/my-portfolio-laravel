<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('seed_key')->nullable()->unique();
            $table->json('title');
            $table->json('description')->nullable();
            $table->enum('category', ['technical', 'interpersonal']);
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['is_visible', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};
