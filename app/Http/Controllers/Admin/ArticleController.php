<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     */
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $query = Article::query()
            ->with('categories')
            ->orderBy('updated_at', 'desc');

        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request): void {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $articles = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.articles.index', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new article.
     */
    public function create(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.articles.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created article.
     */
    public function store(StoreArticleRequest $request)
    {
        $data = $request->validated();

        // Handle photo creation first
        if ($request->hasFile('featured_image_file')) {
            try {
                $photo = $this->createPhotoFromUpload($request->file('featured_image_file'), $data);
                $data['photo_id'] = $photo->id;
            } catch (\Exception $e) {
                \Log::error('Failed to create photo from upload', [
                    'error' => $e->getMessage(),
                ]);

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['featured_image_file' => 'Failed to upload image. Please try again.']);
            }
        } elseif ($request->filled('featured_image') && filter_var($request->featured_image, FILTER_VALIDATE_URL)) {
            try {
                $photo = $this->createPhotoFromUrl($request->featured_image, $data);
                $data['photo_id'] = $photo->id;
            } catch (\Exception $e) {
                \Log::error('Failed to create photo from URL', [
                    'error' => $e->getMessage(),
                ]);

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['featured_image' => 'Failed to process external image URL. Please try again.']);
            }
        } elseif ($request->filled('photo_id')) {
            $data['photo_id'] = $request->photo_id;
        }

        $article = new Article;
        $article->title = $data['title'];
        $article->slug = $data['slug'];
        $article->content = $data['content'];
        $article->status = $data['status'];
        $article->published_at = $data['published_at'] ?? null;
        $article->meta = $data['meta'] ?? [];
        $article->photo_id = $data['photo_id'] ?? null;

        if (empty($data['summary'])) {
            $article->summary = Str::limit(strip_tags((string) $data['content']), 255);
        } else {
            $article->summary = $data['summary'];
        }

        $article->save();

        if (! empty($data['categories'])) {
            $article->categories()->attach($data['categories']);
        }

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Article created successfully.');
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit(Article $article): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $article->load('categories');
        $categories = Category::orderBy('name')->get();

        return view('admin.articles.edit', [
            'article' => $article,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified article.
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $data = $request->validated();

        // Handle featured image removal
        if ($request->boolean('remove_featured_image')) {
            $article->photo_id = null;
        }

        // Handle photo creation/update
        if ($request->hasFile('featured_image_file')) {
            try {
                $photo = $this->createPhotoFromUpload($request->file('featured_image_file'), $data);
                $data['photo_id'] = $photo->id;
            } catch (\Exception $e) {
                \Log::error('Failed to create photo from upload', [
                    'article_id' => $article->id,
                    'error' => $e->getMessage(),
                ]);

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['featured_image_file' => 'Failed to upload image. Please try again.']);
            }
        } elseif ($request->filled('featured_image') && filter_var($request->featured_image, FILTER_VALIDATE_URL)) {
            try {
                $photo = $this->createPhotoFromUrl($request->featured_image, $data);
                $data['photo_id'] = $photo->id;
            } catch (\Exception $e) {
                \Log::error('Failed to create photo from URL', [
                    'article_id' => $article->id,
                    'error' => $e->getMessage(),
                ]);

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['featured_image' => 'Failed to process external image URL. Please try again.']);
            }
        } elseif ($request->filled('photo_id')) {
            $data['photo_id'] = $request->photo_id;
        }

        $article->title = $data['title'];
        $article->slug = $data['slug'];
        $article->content = $data['content'];
        $article->status = $data['status'];
        if (array_key_exists('published_at', $data)) {
            $article->published_at = $data['published_at'];
        }
        $article->meta = $data['meta'] ?? [];
        if (array_key_exists('photo_id', $data)) {
            $article->photo_id = $data['photo_id'];
        }

        if (empty($data['summary'])) {
            $article->summary = Str::limit(strip_tags((string) $data['content']), 255);
        } else {
            $article->summary = $data['summary'];
        }

        $article->save();

        $article->categories()->sync($data['categories'] ?? []);

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Article updated successfully.');
    }

    /**
     * Remove the specified article.
     */
    public function destroy(Article $article)
    {
        $article->categories()->detach();
        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }

    /**
     * Create a Photo from an external URL.
     */
    private function createPhotoFromUrl(string $url, array $articleData): Photo
    {
        $filename = basename(parse_url($url, PHP_URL_PATH)) ?: 'external-image.jpg';
        $slug = Str::slug(pathinfo($filename, PATHINFO_FILENAME));

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while (Photo::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter++;
        }

        return Photo::create([
            'filename' => $filename,
            'slug' => $slug,
            'alt_text' => $articleData['title'] ?? 'Featured image',
            'status' => $articleData['status'] ?? 'draft',
            'published_at' => ($articleData['status'] ?? 'draft') === 'published' ? now() : null,
            'meta' => ['external_url' => $url],
        ]);
    }

    /**
     * Create a Photo from an uploaded file.
     */
    private function createPhotoFromUpload($file, array $articleData): Photo
    {
        $filename = $file->getClientOriginalName();
        $slug = Str::slug(pathinfo((string) $filename, PATHINFO_FILENAME));

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while (Photo::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter++;
        }

        // Extract EXIF if available
        $exif = $this->extractExif($file);

        $photo = Photo::create([
            'filename' => $filename,
            'slug' => $slug,
            'alt_text' => $articleData['title'] ?? 'Featured image',
            'status' => $articleData['status'] ?? 'draft',
            'published_at' => ($articleData['status'] ?? 'draft') === 'published' ? now() : null,
            'meta' => $exif,
        ]);

        // Add file to Photo's media collection
        $disk = $photo->status->isPublic() ? 'public' : 'private';
        $photo->addMedia($file)
            ->toMediaCollection('image', $disk);

        return $photo;
    }

    /**
     * Extract EXIF metadata from uploaded file.
     */
    private function extractExif($file): array
    {
        if (! function_exists('exif_read_data')) {
            return [];
        }

        $exif = @exif_read_data($file->getPathname());

        if (! $exif) {
            return [];
        }

        return array_filter([
            'camera_model' => $exif['Model'] ?? null,
            'lens' => $exif['LensModel'] ?? null,
            'iso' => $exif['ISOSpeedRatings'] ?? null,
            'aperture' => $exif['FNumber'] ?? null,
            'shutter_speed' => $exif['ExposureTime'] ?? null,
            'width' => $exif['COMPUTED']['Width'] ?? null,
            'height' => $exif['COMPUTED']['Height'] ?? null,
        ]);
    }
}
