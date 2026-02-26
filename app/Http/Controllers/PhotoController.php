<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Photo;
use App\Services\ContentFilterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PhotoController extends Controller
{
    public function __construct(
        private readonly ContentFilterService $contentFilter,
    ) {}

    /**
     * Display a listing of photos.
     */
    public function index(Request $request): View
    {
        $photos = $this->contentFilter->filterPhotos($request);

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return view('photos.index', [
            'photos' => $photos,
            'subtitle' => setting('page_photos_subtitle', ''),
            'categories' => $categories,
        ]);
    }

    /**
     * Display the specified photo.
     */
    public function show(string $slug): View|RedirectResponse
    {
        $query = auth()->check() ? Photo::query() : Photo::published();

        $photo = $query->where('slug', $slug)->with('category')->first();

        if ($photo) {
            return view('photos.show', ['photo' => $photo]);
        }

        $photo = Photo::published()->whereJsonContains('past_slugs', $slug)->first();

        if ($photo) {
            return redirect()->route('photos.show', $photo->slug, 301);
        }

        abort(404);
    }
}
