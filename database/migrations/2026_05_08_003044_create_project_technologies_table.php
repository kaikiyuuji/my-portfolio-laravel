<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_technologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stack_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'stack_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_technologies');
    }
};
