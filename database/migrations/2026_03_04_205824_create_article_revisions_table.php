<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['article_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_revisions');
    }
};
