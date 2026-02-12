<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    /**
     * Display a listing of published photos.
     */
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $photos = Photo::published()
            ->orderBy('published_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('photos.index', [
            'photos' => $photos,
        ]);
    }

    /**
     * Display the specified photo.
     */
    public function show(Photo $photo): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        // Return 404 if photo is not public
        if (! $photo->isPublic()) {
            abort(404);
        }

        return view('photos.show', [
            'photo' => $photo,
        ]);
    }
}
