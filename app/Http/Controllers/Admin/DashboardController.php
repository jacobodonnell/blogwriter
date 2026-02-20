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
            ->limit(5)
            ->get();

        $articleCounts = Article::query()
            ->toBase()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $photoCounts = Photo::query()
            ->toBase()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $stats = [
            'total_articles' => $articleCounts->sum(),
            'published_articles' => $articleCounts->get(Status::Published->value, 0),
            'draft_articles' => $articleCounts->get(Status::Draft->value, 0),
            'categories' => Category::count(),
            'total_photos' => $photoCounts->sum(),
            'published_photos' => $photoCounts->get(Status::Published->value, 0),
            'draft_photos' => $photoCounts->get(Status::Draft->value, 0),
        ];

        return view('admin.dashboard', [
            'recentArticles' => $recentArticles,
            'recentPhotos' => $recentPhotos,
            'stats' => $stats,
        ]);
    }
}
