<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Photo;
use Illuminate\View\View;

class CategoryPhotoController extends Controller
{
    /**
     * Display photos by category (including subcategory photos).
     */
    public function index(string $path): View
    {
        $segments = explode('/', $path);
        $parentId = null;

        foreach ($segments as $segment) {
            $category = Category::where('slug', $segment)
                ->where('parent_id', $parentId)
                ->firstOrFail();

            $parentId = $category->id;
        }

        $categoryIds = array_merge([$category->id], $category->descendantIds());

        $photoQuery = auth()->check()
            ? Photo::whereIn('category_id', $categoryIds)
            : Photo::published()->whereIn('category_id', $categoryIds);

        $photos = $photoQuery->orderBy('published_at', 'desc')
            ->paginate(12, ['*'], 'photos_page');

        $children = $category->children()->orderBy('name')->get();

        return view('public.category', [
            'category' => $category,
            'photos' => $photos,
            'children' => $children,
        ]);
    }
}
