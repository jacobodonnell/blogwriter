<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PhotoController extends Controller
{
    /**
     * Display a listing of photos.
     */
    public function index(Request $request): View
    {
        $photoQuery = auth()->check()
            ? Photo::query()
            : Photo::published();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $photoQuery->where(function ($q) use ($search): void {
                $q->where('alt_text', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('slug', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('caption', 'like', sprintf('%%%s%%', $search));
            });
        }

        if ($request->filled('category')) {
            $category = Category::where('slug', $request->input('category'))->first();
            if ($category) {
                $photoQuery->where('category_id', $category->id);
            }
        }

        if (auth()->check() && $request->filled('status')) {
            $photoQuery->where('status', Status::from($request->input('status')));
        }

        $photos = $photoQuery->orderBy('published_at', 'desc')
            ->paginate(12)
            ->withQueryString();

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
