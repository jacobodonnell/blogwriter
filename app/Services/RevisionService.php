<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleRevision;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

final readonly class RevisionService
{
    private Differ $differ;

    public function __construct()
    {
        $this->differ = new Differ(new UnifiedDiffOutputBuilder(''));
    }

    /**
     * Create a revision snapshot if title or content changed.
     *
     * First edit creates a base revision (pre-edit state) + diff revision.
     * Subsequent edits create diff revisions against the latest.
     */
    public function createRevisionIfChanged(Article $article, string $originalTitle, string $originalContent): void
    {
        $currentContent = $article->content ?? '';

        if ($article->title === $originalTitle && $currentContent === $originalContent) {
            return;
        }

        $latestRevision = $article->revisions()->latest('id')->first();

        if (! $latestRevision) {
            $article->revisions()->create([
                'title' => $originalTitle,
                'content' => $originalContent,
            ]);

            $article->revisions()->create([
                'title' => $article->title,
                'content' => $this->generateDiff($originalContent, $currentContent),
            ]);

            return;
        }

        $previousContent = $this->reconstructContent($article, $latestRevision);

        if ($latestRevision->title === $article->title && $previousContent === $currentContent) {
            return;
        }

        $article->revisions()->create([
            'title' => $article->title,
            'content' => $this->generateDiff($previousContent, $currentContent),
        ]);
    }

    /**
     * Generate a unified diff between two markdown strings.
     */
    public function generateDiff(string $old, string $new): string
    {
        return $this->differ->diff($old, $new);
    }

    /**
     * Reconstruct full content for a given revision by replaying the diff chain.
     */
    public function reconstructContent(Article $article, ArticleRevision $target): string
    {
        $revisions = $article->revisions()
            ->where('id', '<=', $target->id)
            ->oldest('id')
            ->get();

        if ($revisions->isEmpty()) {
            return '';
        }

        // First revision is the base (full content)
        $content = $revisions->first()->content;

        // Apply each subsequent diff patch
        foreach ($revisions->skip(1) as $revision) {
            $content = $this->applyPatch($content, $revision->content);
        }

        return $content;
    }

    /**
     * Delete a revision, promoting the next revision to base if deleting the oldest.
     */
    public function deleteRevision(Article $article, ArticleRevision $target): void
    {
        DB::transaction(function () use ($article, $target): void {
            $oldest = $article->revisions()->oldest('id')->first();

            if ($oldest && $oldest->id === $target->id) {
                $next = $article->revisions()
                    ->where('id', '>', $target->id)
                    ->oldest('id')
                    ->first();

                if ($next) {
                    $fullContent = $this->reconstructContent($article, $next);
                    $target->delete();
                    $next->update(['content' => $fullContent]);
                } else {
                    $target->delete();
                }

                return;
            }

            $next = $article->revisions()
                ->where('id', '>', $target->id)
                ->oldest('id')
                ->first();

            if ($next) {
                $nextFullContent = $this->reconstructContent($article, $next);

                $before = $article->revisions()
                    ->where('id', '<', $target->id)
                    ->latest('id')
                    ->first();

                $beforeContent = $before ? $this->reconstructContent($article, $before) : '';

                $target->delete();

                $next->update(['content' => $this->generateDiff($beforeContent, $nextFullContent)]);
            } else {
                $target->delete();
            }
        });
    }

    /**
     * Apply a unified diff patch to a source string.
     *
     * Parses `@@ -start,count +start,count @@` hunks and applies
     * additions/removals to produce the patched output.
     */
    public function applyPatch(string $source, string $patch): string
    {
        $sourceLines = $source === '' ? [] : explode("\n", $source);
        $patchLines = explode("\n", mb_rtrim($patch, "\n"));
        $result = [];
        $sourceIndex = 0;

        $i = 0;
        $patchLineCount = count($patchLines);

        while ($i < $patchLineCount) {
            $line = $patchLines[$i];

            // Look for hunk headers: @@ -start,count +start,count @@ or @@ @@
            if (preg_match('/^@@ (?:-(\d+)(?:,(\d+))? \+(\d+)(?:,(\d+))? )?@@/', $line, $matches)) {
                $oldStart = isset($matches[1]) && $matches[1] !== '' ? (int) $matches[1] : 1;

                // Copy lines before this hunk (1-indexed to 0-indexed)
                while ($sourceIndex < $oldStart - 1 && $sourceIndex < count($sourceLines)) {
                    $result[] = $sourceLines[$sourceIndex];
                    $sourceIndex++;
                }

                $i++;

                // Process hunk lines
                while ($i < $patchLineCount && ! preg_match('/^@@/', $patchLines[$i])) {
                    $hunkLine = $patchLines[$i];

                    if ($hunkLine === '') {
                        // Empty line in a diff is a context line representing a blank source line
                        $result[] = '';
                        $sourceIndex++;
                        $i++;

                        continue;
                    }

                    $prefix = $hunkLine[0];
                    $content = mb_substr($hunkLine, 1);

                    if ($prefix === ' ') {
                        // Context line — keep and advance source
                        $result[] = $content;
                        $sourceIndex++;
                    } elseif ($prefix === '-') {
                        // Removed line — skip source line
                        $sourceIndex++;
                    } elseif ($prefix === '+') {
                        // Added line — add to result
                        $result[] = $content;
                    }

                    $i++;
                }

                continue;
            }

            $i++;
        }

        // Copy remaining source lines after the last hunk
        while ($sourceIndex < count($sourceLines)) {
            $result[] = $sourceLines[$sourceIndex];
            $sourceIndex++;
        }

        return implode("\n", $result);
    }
}
