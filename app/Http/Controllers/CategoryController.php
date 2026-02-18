<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of root categories.
     */
    public function index(): View
    {
        $categories = Category::whereNull('parent_id')
            ->withCount(['articles', 'children', 'photos'])
            ->orderBy('name')
            ->get();

        return view('public.categories', [
            'categories' => $categories,
        ]);
    }
}
