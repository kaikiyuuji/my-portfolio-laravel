<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a singleton constraint to the profiles table.
     *
     * The `singleton_key` column defaults to `true` (1) and has a UNIQUE index,
     * which prevents more than one row from ever existing in this table.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('singleton_key')->default(true)->unique()
                ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropUnique(['singleton_key']);
            $table->dropColumn('singleton_key');
        });
    }
};
