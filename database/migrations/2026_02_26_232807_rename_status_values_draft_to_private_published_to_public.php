<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * SQLite bakes CHECK constraints into the table schema for enum columns,
     * so we must preserve values in a temp column, drop the index + column,
     * recreate with new enum values, then restore data.
     */
    public function up(): void
    {
        // --- Articles (enum column with CHECK constraint + index) ---
        Schema::table('articles', function (Blueprint $table): void {
            $table->string('status_old')->nullable();
        });

        DB::table('articles')->update(['status_old' => DB::raw('"status"')]);

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropIndex('articles_status_index');
            $table->dropColumn('status');
        });
        Schema::table('articles', function (Blueprint $table): void {
            $table->enum('status', ['private', 'public'])->default('private')->after('draft');
            $table->index('status');
        });

        DB::table('articles')->where('status_old', 'published')->update(['status' => 'public']);
        DB::table('articles')->where('status_old', 'draft')->update(['status' => 'private']);

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn('status_old');
        });

        // --- Photos (plain string column — no CHECK constraint) ---
        DB::table('photos')->where('status', 'draft')->update(['status' => 'private']);
        DB::table('photos')->where('status', 'published')->update(['status' => 'public']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->string('status_old')->nullable();
        });

        DB::table('articles')->update(['status_old' => DB::raw('"status"')]);

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropIndex('articles_status_index');
            $table->dropColumn('status');
        });
        Schema::table('articles', function (Blueprint $table): void {
            $table->enum('status', ['draft', 'published'])->default('draft')->after('draft');
            $table->index('status');
        });

        DB::table('articles')->where('status_old', 'public')->update(['status' => 'published']);
        DB::table('articles')->where('status_old', 'private')->update(['status' => 'draft']);

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn('status_old');
        });

        DB::table('photos')->where('status', 'private')->update(['status' => 'draft']);
        DB::table('photos')->where('status', 'public')->update(['status' => 'published']);
    }
};
