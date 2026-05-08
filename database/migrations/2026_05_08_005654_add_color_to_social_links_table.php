<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_links', function (Blueprint $table) {
            $table->string('color')->nullable()->after('icon_slug');
        });
    }

    public function down(): void
    {
        Schema::table('social_links', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
