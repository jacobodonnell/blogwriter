<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of root categories.
     */
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->withCount(['articles', 'children'])
            ->orderBy('name')
            ->get();

        $viewData = [
            'categories' => $categories,
            'parent' => null,
            'breadcrumbs' => collect(),
            'slugPrefix' => '',
        ];

        if ($request->header('X-Alpine-Target')) {
            return view('admin.categories._table', $viewData);
        }

        return view('admin.categories.index', $viewData);
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request): Response|RedirectResponse
    {
        $data = $request->validated();

        Category::create($data);

        if ($request->header('X-Alpine-Target')) {
            return response(view('admin.categories._store-success'));
        }

        return redirect($this->categoryRedirectUrl($data['parent_id'] ?? null))
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        $excludeIds = array_merge([$category->id], $category->descendantIds());

        $allCategories = Category::query()
            ->whereNotIn('id', $excludeIds)
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', [
            'category' => $category,
            'allCategories' => $allCategories,
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $redirectUrl = $this->categoryRedirectUrl($category->parent_id);

        if ($category->children()->count() > 0) {
            return redirect($redirectUrl)
                ->with('error', 'Cannot delete category with subcategories. Remove subcategories first.');
        }

        if ($category->articles()->count() > 0) {
            return redirect($redirectUrl)
                ->with('error', 'Cannot delete category with articles. Remove articles first.');
        }

        $category->delete();

        return redirect($redirectUrl)
            ->with('success', 'Category deleted successfully.');
    }

    private function categoryRedirectUrl(?int $parentId): string
    {
        if (! $parentId) {
            return route('admin.categories.index');
        }

        $parent = Category::find($parentId);

        if (! $parent) {
            return route('admin.categories.index');
        }

        $slugs = $parent->ancestors()->pluck('slug')->push($parent->slug)->implode('/');

        return route('admin.categories.children', $slugs);
    }
}
