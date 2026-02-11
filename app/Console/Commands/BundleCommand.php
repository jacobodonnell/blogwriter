<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use ZipArchive;

class BundleCommand extends Command
{
    protected $signature = 'blogwriter:bundle
                            {--skip-build : Skip npm run build step}';

    protected $description = 'Create a distributable ZIP package of BlogWriter';

    public function handle(): int
    {
        $this->info('Creating BlogWriter distribution bundle...');

        // Check if npm build exists
        if (! $this->option('skip-build') && ! is_dir(public_path('build'))) {
            $this->warn('No build directory found. Running npm run build...');
            $process = Process::fromShellCommandline('npm run build');
            $process->run();
            if (! $process->isSuccessful()) {
                $this->error('npm run build failed. Use --skip-build to skip this step.');

                return self::FAILURE;
            }
        }

        // Create dist directory
        $distPath = base_path('dist');
        if (! is_dir($distPath)) {
            mkdir($distPath, 0755, true);
        }

        // Get version from composer.json or default
        $version = '0.1a';
        $zipName = "blogwriter-v{$version}.zip";
        $zipPath = $distPath.'/'.$zipName;

        // Remove existing zip
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $this->info('Creating ZIP archive...');

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Cannot create ZIP archive');

            return self::FAILURE;
        }

        // Add files recursively
        $this->addDirectoryToZip($zip, base_path(), '', [
            '.git',
            '.gitignore',
            'node_modules',
            'dist',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            '.env',
            'tests',
            'phpunit.xml',
            'pest.xml',
            '.DS_Store',
            '*.log',
        ]);

        // Ensure required storage directories exist in bundle
        $this->addStorageDirectories($zip);

        $zip->close();

        $size = $this->formatBytes(filesize($zipPath));
        $this->info("✓ Bundle created: {$zipPath}");
        $this->info("  Size: {$size}");
        $this->newLine();
        $this->info('To install on a server:');
        $this->info('1. Upload the ZIP file to your server');
        $this->info('2. Extract the contents');
        $this->info('3. Run: php artisan blogwriter:install');

        return self::SUCCESS;
    }

    protected function addDirectoryToZip(ZipArchive $zip, string $source, string $destination, array $exclude): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr((string) $filePath, strlen($source) + 1);

            // Check exclusions
            $shouldExclude = false;
            foreach ($exclude as $pattern) {
                // Special handling for .env - only exclude exact match, not .env.* files
                if ($pattern === '.env') {
                    if (basename($relativePath) === '.env') {
                        $shouldExclude = true;
                        break;
                    }

                    continue; // Don't exclude .env.example, .env.freshinstall, etc.
                }

                if (str_starts_with($relativePath, (string) $pattern) || fnmatch($pattern, basename($relativePath))) {
                    $shouldExclude = true;
                    break;
                }
            }

            if ($shouldExclude) {
                continue;
            }

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes > 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2).' '.$units[$unitIndex];
    }

    /**
     * Add required storage directories to the ZIP archive.
     * These directories are excluded from the file scan but must exist
     * for Laravel to function properly on fresh installations.
     */
    protected function addStorageDirectories(ZipArchive $zip): void
    {
        $requiredDirs = [
            'storage/framework/cache',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'storage/logs',
        ];

        foreach ($requiredDirs as $dir) {
            // Only add if not already present
            if ($zip->locateName($dir.'/.gitkeep') === false && $zip->locateName($dir) === false) {
                $zip->addEmptyDir($dir);
                $this->info("  Added directory: {$dir}");
            }
        }
    }
}
