<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories, optionally scoped to a parent.
     */
    public function index(Request $request): View
    {
        $parentId = $request->input('parent');
        $parent = $parentId ? Category::findOrFail($parentId) : null;

        $categories = Category::query()
            ->where('parent_id', $parent?->id)
            ->withCount(['articles', 'children'])
            ->orderBy('name')
            ->get();

        $breadcrumbs = $parent ? $parent->ancestors()->push($parent) : collect();

        $allCategories = Category::query()
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $viewData = [
            'categories' => $categories,
            'parent' => $parent,
            'breadcrumbs' => $breadcrumbs,
            'allCategories' => $allCategories,
        ];

        if ($request->header('X-Alpine-Target')) {
            return view('admin.categories._table', $viewData);
        }

        return view('admin.categories.index', $viewData);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Category::create($data);

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
    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
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
        $url = route('admin.categories.index');

        if ($parentId) {
            $url .= '?parent='.$parentId;
        }

        return $url;
    }
}
