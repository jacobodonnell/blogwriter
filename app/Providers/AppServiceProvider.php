<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Resettable;
use App\Models\Photo;
use App\Models\User;
use App\Observers\PhotoObserver;
use App\Services\ResetService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use PDOException;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (empty(config('app.key'))) {
            config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        }

        $this->app->bind(Resettable::class, ResetService::class);
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
            'admin.categories._explore-content',
        ], function ($view): void {
            try {
                $authorName = Cache::remember(
                    'author_name',
                    now()->addHour(),
                    fn () => User::first()?->name ?? 'Author',
                );
            } catch (QueryException|PDOException) {
                $authorName = 'Author';
            }

            $view->with('authorName', $authorName);
        });
    }
}
