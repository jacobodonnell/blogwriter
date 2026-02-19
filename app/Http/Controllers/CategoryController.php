<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display all content across all categories (root feed).
     */
    public function index(Request $request): View
    {
        $isAuth = auth()->check();

        $type = $request->query('type', 'all');

        if (! in_array($type, ['all', 'articles', 'photos'])) {
            $type = 'all';
        }

        $search = $request->query('search');
        $status = $isAuth && $request->filled('status')
            ? Status::from($request->input('status'))
            : null;

        $articles = new LengthAwarePaginator([], 0, 10);
        $photos = new LengthAwarePaginator([], 0, 12);

        if ($type === 'all' || $type === 'articles') {
            $articleQuery = $isAuth
                ? Article::query()
                : Article::published();

            if ($search) {
                $articleQuery->where('title', 'like', sprintf('%%%s%%', $search));
            }

            if ($status) {
                $articleQuery->where('status', $status);
            }

            $articles = $articleQuery->with('category')
                ->orderBy('published_at', 'desc')
                ->paginate(10, ['*'], 'articles_page')
                ->withQueryString();
        }

        if ($type === 'all' || $type === 'photos') {
            $photoQuery = $isAuth
                ? Photo::query()
                : Photo::published();

            if ($search) {
                $photoQuery->where(function ($q) use ($search): void {
                    $q->where('alt_text', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('caption', 'like', sprintf('%%%s%%', $search));
                });
            }

            if ($status) {
                $photoQuery->where('status', $status);
            }

            $photos = $photoQuery->orderBy('published_at', 'desc')
                ->paginate(12, ['*'], 'photos_page')
                ->withQueryString();
        }

        $articleCount = $isAuth
            ? Article::count()
            : Article::published()->count();

        $photoCount = $isAuth
            ? Photo::count()
            : Photo::published()->count();

        $children = Category::tree()->get();

        return view('public.categories', [
            'children' => $children,
            'articles' => $articles,
            'photos' => $photos,
            'currentType' => $type,
            'articleCount' => $articleCount,
            'photoCount' => $photoCount,
        ]);
    }
}
