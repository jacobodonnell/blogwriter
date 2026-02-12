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
        Schema::table('articles', function (Blueprint $table): void {
            $table->integer('featured_image_width')->nullable()->after('featured_image');
            $table->integer('featured_image_height')->nullable()->after('featured_image_width');
            $table->integer('featured_image_file_size')->nullable()->after('featured_image_height');
            $table->string('featured_image_mime_type')->nullable()->after('featured_image_file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn('featured_image_width');
            $table->dropColumn('featured_image_height');
            $table->dropColumn('featured_image_file_size');
            $table->dropColumn('featured_image_mime_type');
        });
    }
};
