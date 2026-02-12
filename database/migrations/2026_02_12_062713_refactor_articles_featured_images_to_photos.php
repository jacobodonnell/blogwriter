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
        Schema::table('articles', function (Blueprint $table) {
            // Remove old featured image columns
            $table->dropColumn([
                'featured_image',
                'featured_image_width',
                'featured_image_height',
                'featured_image_file_size',
                'featured_image_mime_type',
            ]);

            // Add Photo relationship
            $table->foreignId('photo_id')
                ->nullable()
                ->after('meta')
                ->constrained('photos')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['photo_id']);
            $table->dropColumn('photo_id');

            // Restore old columns
            $table->string('featured_image')->nullable()->after('meta');
            $table->integer('featured_image_width')->nullable();
            $table->integer('featured_image_height')->nullable();
            $table->integer('featured_image_file_size')->nullable();
            $table->string('featured_image_mime_type')->nullable();
        });
    }
};
