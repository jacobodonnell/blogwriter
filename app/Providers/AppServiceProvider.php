<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Photo;
use App\Models\User;
use App\Observers\ArticleObserver;
use App\Observers\PhotoObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Article::observe(ArticleObserver::class);
        Photo::observe(PhotoObserver::class);

        View::composer([
            'public.*',
            'photos.*',
            'components.seo-meta',
            'components.layouts.partials.public-footer',
        ], function ($view): void {
            $view->with('authorName', User::first()?->name ?? 'Author');
        });
    }
}
