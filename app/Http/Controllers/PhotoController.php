<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Photo;
use Illuminate\View\View;

class PhotoController extends Controller
{
    /**
     * Display a listing of published photos.
     */
    public function index(): View
    {
        $photos = Photo::published()
            ->orderBy('published_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $categories = auth()->check()
            ? Category::whereNull('parent_id')->with('children')->orderBy('name')->get()
            : collect();

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
        // Return 404 if photo is not public
        if (! $photo->isPublic()) {
            abort(404);
        }

        $photo->load('category');

        return view('photos.show', [
            'photo' => $photo,
        ]);
    }
}
