<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Yaml\Yaml;
use ZipStream\ZipStream;

final class ArticleExportService
{
    /**
     * Stream all articles as a ZIP of Markdown files into the given ZipStream.
     *
     * @param  Collection<int, Article>  $articles
     */
    public function streamToZip(ZipStream $zip, Collection $articles): void
    {
        foreach ($articles as $article) {
            $frontmatter = $this->buildFrontmatter($article);
            $content = $article->getAttributes()['content'] ?? '';
            $fileContent = "---\n".Yaml::dump($frontmatter, 2, 2)."---\n\n".$content;

            $zip->addFile(sprintf('articles/%s.md', $article->slug), $fileContent);
        }
    }

    /**
     * Stream all categories as a YAML file into the given ZipStream.
     */
    public function streamCategoriesToZip(ZipStream $zip): void
    {
        $categories = Category::query()->with('parent')->orderBy('id')->get();

        $data = $categories->map(fn (Category $category): array => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'parent_slug' => $category->parent?->slug,
        ])->values()->all();

        $zip->addFile('categories.yaml', Yaml::dump($data, 2, 2));
    }

    /**
     * Stream site settings as a settings.yaml file into the given ZipStream.
     */
    public function streamSettingsToZip(ZipStream $zip): void
    {
        $settingKeys = [
            'theme_light', 'theme_dark', 'theme_font',
            'profile_email', 'profile_avatar', 'profile_bio',
            'profile_github', 'profile_mastodon', 'profile_bluesky',
            'page_home_subtitle', 'page_articles_subtitle', 'page_photos_subtitle',
        ];

        $data = ['profile_name' => User::query()->value('name')];

        $settings = Setting::query()->whereIn('key', $settingKeys)->pluck('value', 'key')->all();

        foreach ($settingKeys as $key) {
            if (isset($settings[$key])) {
                $data[$key] = $settings[$key];
            }
        }

        $zip->addFile('settings.yaml', Yaml::dump($data, 2, 2));
    }

    /**
     * Build the YAML frontmatter array for an article.
     *
     * @return array<string, mixed>
     */
    public function buildFrontmatter(Article $article): array
    {
        $frontmatter = [
            'title' => $article->title,
            'date' => $article->published_at?->utc()->toIso8601String()
                                        ?? $article->created_at->utc()->toIso8601String(),
            'created_at' => $article->created_at->utc()->toIso8601String(),
            'last_edited_at' => $article->last_edited_at?->utc()->toIso8601String(),
            'slug' => $article->slug,
            'draft' => $article->status === Status::Draft,
            'description' => $article->summary ?: null,
            'author' => $article->user?->name,
            'category' => $article->category?->slug,
            'past_slugs' => array_values($article->past_slugs ?? []),
            'meta_title' => $article->meta['meta_title'] ?? null,
            'meta_description' => $article->meta['meta_description'] ?? null,
            'featured_image_url' => $article->external_featured_img_url,
            'photo_slug' => $article->featuredPhoto?->slug,
            'featured_image_caption' => $article->featured_image_caption ?: null,
            'featured_image_alt' => $article->meta['featured_image_alt']
                                        ?? $article->featuredPhoto?->alt_text
                                        ?: null,
            'og_image' => $article->meta['og_image'] ?? null,
        ];

        return array_filter($frontmatter, fn ($value): bool => $value !== null);
    }

    /**
     * Stream all photos as a photos.yaml file into the given ZipStream.
     */
    public function streamPhotosToZip(ZipStream $zip): void
    {
        $photos = Photo::query()->with('category', 'media')->orderBy('id')->get();

        $data = $photos->map(fn (Photo $photo): array => array_filter([
            'slug' => $photo->slug,
            'filename' => $photo->filename,
            'caption' => $photo->caption,
            'alt_text' => $photo->alt_text,
            'status' => $photo->status->value,
            'published_at' => $photo->published_at?->utc()->toIso8601String(),
            'taken_at' => $photo->taken_at?->utc()->toIso8601String(),
            'category' => $photo->category?->slug,
            'meta' => $photo->meta ?: null,
            'image_file' => $this->photoImageFilename($photo),
        ], fn ($v): bool => $v !== null))->values()->all();

        $zip->addFile('photos.yaml', Yaml::dump($data, 3, 2));
    }

    /**
     * Stream original photo image files into the images/ directory in the ZipStream.
     */
    public function streamPhotoImagesToZip(ZipStream $zip): void
    {
        $photos = Photo::query()->with('media')->get();

        foreach ($photos as $photo) {
            $media = $photo->getFirstMedia('image');
            if ($media === null) {
                continue;
            }

            $path = $media->getPath();
            if (! file_exists($path)) {
                continue;
            }

            $zip->addFileFromPath(
                'images/'.$this->photoImageFilename($photo),
                $path,
            );
        }
    }

    /**
     * Build the filename used for a photo's image file in the export.
     */
    private function photoImageFilename(Photo $photo): ?string
    {
        $media = $photo->getFirstMedia('image');
        if (! $media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
            return null;
        }

        return $media->uuid.'-'.$media->file_name;
    }
}
