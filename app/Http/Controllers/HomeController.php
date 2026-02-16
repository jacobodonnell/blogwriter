<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Photo;

class HomeController extends Controller
{
    /**
     * Display the homepage with recent articles and photos sidebar.
     */
    public function index(): \Illuminate\View\View
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
        ]);
    }
}
