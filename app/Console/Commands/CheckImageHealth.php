<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CheckImageHealth extends Command
{
    protected $signature = 'images:check {--fix : Automatically fix issues}';

    protected $description = 'Check and optionally repair article image issues';

    public function handle(): int
    {
        $this->info('Checking article image health...');
        $fix = $this->option('fix');

        $issues = [
            'missing_files' => 0,
            'orphaned_media' => 0,
            'wrong_disk' => 0,
            'duplicates' => 0,
            'missing_conversions' => 0,
        ];

        // Check 1: Media records without files
        $this->info("\n[1] Checking for media records with missing files...");
        $media = Media::where('model_type', Article::class)->get();

        foreach ($media as $mediaItem) {
            if (! Storage::disk($mediaItem->disk)->exists($mediaItem->getPath())) {
                $issues['missing_files']++;
                $this->warn(sprintf('  ⚠ Media #%s: File missing on %s disk', $mediaItem->id, $mediaItem->disk));

                if ($fix) {
                    $mediaItem->delete();
                    $this->info('    ✓ Deleted orphaned media record');
                }
            }
        }

        // Check 2: Orphaned media (article deleted but media remains)
        $this->info("\n[2] Checking for orphaned media records...");
        $orphanedMedia = Media::where('model_type', Article::class)
            ->whereDoesntHave('model')
            ->get();

        foreach ($orphanedMedia as $mediaItem) {
            $issues['orphaned_media']++;
            $this->warn(sprintf('  ⚠ Media #%s: Article deleted but media remains', $mediaItem->id));

            if ($fix) {
                $mediaItem->delete(); // Spatie automatically deletes files
                $this->info('    ✓ Deleted orphaned media and files');
            }
        }

        // Check 3: Media on wrong disk (based on article status)
        $this->info("\n[3] Checking for media on wrong disk...");
        $articles = Article::has('media')->get();

        foreach ($articles as $article) {
            $expectedDisk = $article->status === Status::Published ? 'public' : 'private';
            $media = $article->getFirstMedia('featured_image');

            if ($media && $media->disk !== $expectedDisk) {
                $issues['wrong_disk']++;
                $this->warn(sprintf('  ⚠ Article #%s: Image on %s disk, should be on %s', $article->id, $media->disk, $expectedDisk));

                if ($fix) {
                    // Move to correct disk
                    $media->move($article, 'featured_image', $expectedDisk);
                    $this->info(sprintf('    ✓ Moved to %s disk', $expectedDisk));
                }
            }
        }

        // Check 4: Duplicate files (same article with multiple featured images)
        $this->info("\n[4] Checking for duplicate featured images...");
        $articles = Article::has('media')->get();

        foreach ($articles as $article) {
            $mediaCount = $article->getMedia('featured_image')->count();

            if ($mediaCount > 1) {
                $issues['duplicates']++;
                $this->warn(sprintf('  ⚠ Article #%s: Has %s featured images (should have 1)', $article->id, $mediaCount));

                if ($fix) {
                    // Keep the newest, delete others
                    $allMedia = $article->getMedia('featured_image');
                    $keep = $allMedia->last();

                    foreach ($allMedia as $mediaItem) {
                        if ($mediaItem->id !== $keep->id) {
                            $mediaItem->delete();
                        }
                    }

                    $this->info('    ✓ Kept newest image, deleted '.($mediaCount - 1).' duplicates');
                }
            }
        }

        // Check 5: Missing conversions
        $this->info("\n[5] Checking for missing image conversions...");
        $media = Media::where('model_type', Article::class)
            ->where('collection_name', 'featured_image')
            ->get();

        foreach ($media as $mediaItem) {
            $expectedConversions = ['thumbnail', 'medium', 'large'];

            foreach ($expectedConversions as $conversion) {
                if (! $mediaItem->hasGeneratedConversion($conversion)) {
                    $issues['missing_conversions']++;
                    $this->warn(sprintf("  ⚠ Media #%s: Missing '%s' conversion", $mediaItem->id, $conversion));

                    if ($fix) {
                        // Regenerate all conversions
                        $mediaItem->model->registerMediaConversions();
                        $this->info('    ✓ Regenerated conversions');
                        break; // Only log once per media item
                    }
                }
            }
        }

        // Summary
        $this->info("\n".str_repeat('=', 60));
        $this->info('Health Check Summary:');
        $this->info(str_repeat('=', 60));

        $totalIssues = array_sum($issues);

        if ($totalIssues === 0) {
            $this->info('✓ No issues found! All images are healthy.');
        } else {
            foreach ($issues as $type => $count) {
                if ($count > 0) {
                    $label = str_replace('_', ' ', ucfirst($type));
                    $this->warn(sprintf('  %s: %d', $label, $count));
                }
            }

            if (! $fix) {
                $this->info("\nRun with --fix to automatically repair these issues:");
                $this->info('  php artisan images:check --fix');
            } else {
                $this->info("\n✓ Repair complete!");
            }
        }

        return 0;
    }
}
