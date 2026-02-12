<?php

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('seeder tests', function (): void {
    it('seeds categories with correct data', function (): void {
        $seeder = new DatabaseSeeder;
        $seeder->withState('empty')->seed();

        expect(Category::count())->toBe(5)
            ->and(Category::where('name', 'General')->exists())->toBeTrue()
            ->and(Category::where('name', 'Technology')->exists())->toBeTrue();
    });

    it('demo state seeds articles with mixed statuses', function (): void {
        $seeder = new DatabaseSeeder;
        $seeder->withState('demo')->seed();

        expect(Article::count())->toBeGreaterThanOrEqual(5);

        $publishedCount = Article::where('status', Status::Published)->count();
        $draftCount = Article::where('status', Status::Draft)->count();

        expect($publishedCount)->toBeGreaterThan(0);
        expect($draftCount)->toBeGreaterThan(0);
    });

    it('empty state seeds only categories', function (): void {
        $seeder = new DatabaseSeeder;
        $seeder->withState('empty')->seed();

        expect(Category::count())->toBe(5);
        expect(Article::count())->toBe(0);
    });

    it('seeding creates expected data', function (): void {
        $seeder = new DatabaseSeeder;
        $seeder->withState('demo')->seed();

        expect(Category::count())->toBeGreaterThan(0);
        expect(Article::count())->toBeGreaterThan(0);
    });

    it('seeded published articles have valid published_at dates', function (): void {
        $seeder = new DatabaseSeeder;
        $seeder->withState('demo')->seed();

        $publishedArticles = Article::where('status', Status::Published)->get();

        foreach ($publishedArticles as $article) {
            expect($article->published_at)->not->toBeNull();
        }
    });
})->group('slow');
