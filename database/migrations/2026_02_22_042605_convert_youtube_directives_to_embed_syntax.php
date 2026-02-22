<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert :::youtube {src="url" ...} ::: directives to @[youtube](url) syntax.
     */
    public function up(): void
    {
        DB::table('articles')
            ->where('content', 'like', '%:::youtube%')
            ->eachById(function (object $article): void {
                $content = preg_replace(
                    '/:::youtube\s+\{src="([^"]+)"[^}]*\}\s*:::/',
                    '@[youtube]($1)',
                    (string) $article->content,
                );

                DB::table('articles')
                    ->where('id', $article->id)
                    ->update(['content' => $content]);
            });
    }
};
