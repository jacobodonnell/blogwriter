<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Photo;
use Illuminate\View\View;

final class HomeController extends Controller
{
    /**
     * Display the homepage with recent articles and photos sidebar.
     */
    public function index(): View
    {
        $articles = Article::published()
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        $photos = Photo::published()
            ->orderBy('published_at', 'desc')
            ->limit(9)
            ->get();

        return view('public.index', [
            'articles' => $articles,
            'photos' => $photos,
            'subtitle' => setting('page_home_subtitle', ''),
        ]);
    }
}
