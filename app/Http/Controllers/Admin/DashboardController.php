<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function __invoke(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $recentArticles = Article::query()
            ->with('categories')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        $stats = [
            'total_articles' => Article::count(),
            'published_articles' => Article::where('status', 'published')->count(),
            'draft_articles' => Article::where('status', 'draft')->count(),
            'hidden_articles' => Article::where('status', 'hidden')->count(),
            'total_categories' => Category::count(),
        ];

        return view('admin.dashboard', [
            'recentArticles' => $recentArticles,
            'stats' => $stats,
        ]);
    }
}
