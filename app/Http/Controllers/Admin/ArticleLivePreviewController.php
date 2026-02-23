<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\View\View;

final class ArticleLivePreviewController extends Controller
{
    /**
     * Return the preview partial for the committed live content (no draft applied).
     */
    public function __invoke(Article $article): View
    {
        $article->load('category');

        return view('admin.articles.preview', ['article' => $article]);
    }
}
