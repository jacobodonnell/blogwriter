<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of root categories.
     */
    public function index(): View
    {
        $isAuth = auth()->check();

        $categories = Category::whereNull('parent_id')
            ->withCount([
                'articles' => fn ($q) => $isAuth ? $q : $q->where('status', Status::Published),
                'photos' => fn ($q) => $isAuth ? $q : $q->where('status', Status::Published),
                'children',
            ])
            ->orderBy('name')
            ->get();

        return view('public.categories', [
            'categories' => $categories,
        ]);
    }
}
