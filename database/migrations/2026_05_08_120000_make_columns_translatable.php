<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert text columns to JSON for spatie/laravel-translatable.
     * Existing strings are wrapped as {"pt": "<value>"} during backfill.
     */
    public function up(): void
    {
        $this->convertTable('profiles', ['headline', 'bio']);
        $this->convertTable('experiences', ['company', 'role', 'description']);
        $this->convertTable('projects', ['title', 'description']);
    }

    public function down(): void
    {
        $this->revertTable('profiles', ['headline', 'bio']);
        $this->revertTable('experiences', ['company', 'role', 'description']);
        $this->revertTable('projects', ['title', 'description']);
    }

    private function convertTable(string $table, array $columns): void
    {
        Schema::table($table, function (Blueprint $t) use ($columns) {
            foreach ($columns as $col) {
                $t->json("{$col}_new")->nullable();
            }
        });

        DB::table($table)->orderBy('id')->each(function ($row) use ($table, $columns) {
            $update = [];
            foreach ($columns as $col) {
                $value = $row->{$col} ?? '';
                $update["{$col}_new"] = json_encode(['pt' => $value], JSON_UNESCAPED_UNICODE);
            }
            DB::table($table)->where('id', $row->id)->update($update);
        });

        Schema::table($table, function (Blueprint $t) use ($columns) {
            foreach ($columns as $col) {
                $t->dropColumn($col);
            }
        });

        Schema::table($table, function (Blueprint $t) use ($columns) {
            foreach ($columns as $col) {
                $t->renameColumn("{$col}_new", $col);
            }
        });
    }

    private function revertTable(string $table, array $columns): void
    {
        Schema::table($table, function (Blueprint $t) use ($columns) {
            foreach ($columns as $col) {
                $t->text("{$col}_old")->nullable();
            }
        });

        DB::table($table)->orderBy('id')->each(function ($row) use ($table, $columns) {
            $update = [];
            foreach ($columns as $col) {
                $decoded = json_decode($row->{$col} ?? '{}', true);
                $update["{$col}_old"] = $decoded['pt'] ?? ($decoded['en'] ?? '');
            }
            DB::table($table)->where('id', $row->id)->update($update);
        });

        Schema::table($table, function (Blueprint $t) use ($columns) {
            foreach ($columns as $col) {
                $t->dropColumn($col);
            }
        });

        Schema::table($table, function (Blueprint $t) use ($columns) {
            foreach ($columns as $col) {
                $t->renameColumn("{$col}_old", $col);
            }
        });
    }
};
