<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function __invoke(): View
    {
        $recentArticles = Article::query()
            ->with('category')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        $recentPhotos = Photo::query()
            ->with('media')
            ->orderBy('updated_at', 'desc')
            ->limit(4)
            ->get();

        $stats = [
            'total_articles' => Article::count(),
            'published_articles' => Article::where('status', Status::Published)->count(),
            'draft_articles' => Article::where('status', Status::Draft)->count(),
            'categories' => Category::count(),
            'total_photos' => Photo::count(),
            'published_photos' => Photo::where('status', Status::Published)->count(),
            'draft_photos' => Photo::where('status', Status::Draft)->count(),
        ];

        return view('admin.dashboard', [
            'recentArticles' => $recentArticles,
            'recentPhotos' => $recentPhotos,
            'stats' => $stats,
        ]);
    }
}
