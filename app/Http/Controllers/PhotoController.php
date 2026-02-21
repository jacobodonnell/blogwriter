<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Photo;
use App\Services\ContentFilterService;
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
    public function show(Photo $photo): View
    {
        // Auth users can view any photo, guests only public
        if (! auth()->check() && ! $photo->isPublic()) {
            abort(404);
        }

        $photo->load('category');

        return view('photos.show', [
            'photo' => $photo,
        ]);
    }
}
