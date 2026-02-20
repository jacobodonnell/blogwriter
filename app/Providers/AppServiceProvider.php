<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Photo;
use App\Models\User;
use App\Observers\PhotoObserver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
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
        Photo::observe(PhotoObserver::class);

        View::composer([
            'public.*',
            'photos.*',
            'components.seo-meta',
            'components.layouts.partials.public-footer',
            'components.category-feed',
            'components.category-layout',
        ], function ($view): void {
            $view->with('authorName', Cache::remember(
                'author_name',
                now()->addHour(),
                fn () => User::first()?->name ?? 'Author',
            ));
        });
    }
}
