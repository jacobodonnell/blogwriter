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
        Schema::create('system_events', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->index(); // e.g., 'storage_repair', 'install_error', 'permission_denied'
            $table->text('message');
            $table->json('context')->nullable(); // Additional data like paths, permissions, etc.
            $table->string('severity')->default('info'); // info, warning, error, critical
            $table->boolean('is_error_log')->default(false)->index(); // Flag for filtering error logs
            $table->boolean('resolved')->default(false)->index(); // For tracking if issue was fixed
            $table->text('admin_notes')->nullable(); // Manual notes for later GUI
            $table->timestamp('acknowledged_at')->nullable(); // When admin acknowledged
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_events');
    }
};
