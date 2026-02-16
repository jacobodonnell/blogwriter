<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories, optionally scoped to a parent.
     */
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
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
    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        Category::create($data);

        $redirectUrl = route('admin.categories.index');
        if (! empty($data['parent_id'])) {
            $redirectUrl .= '?parent='.$data['parent_id'];
        }

        return redirect($redirectUrl)
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
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
    public function update(StoreCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        if ($category->children()->count() > 0) {
            $redirectUrl = route('admin.categories.index');
            if ($category->parent_id) {
                $redirectUrl .= '?parent='.$category->parent_id;
            }

            return redirect($redirectUrl)
                ->with('error', 'Cannot delete category with subcategories. Remove subcategories first.');
        }

        if ($category->articles()->count() > 0) {
            $redirectUrl = route('admin.categories.index');
            if ($category->parent_id) {
                $redirectUrl .= '?parent='.$category->parent_id;
            }

            return redirect($redirectUrl)
                ->with('error', 'Cannot delete category with articles. Remove articles first.');
        }

        $parentId = $category->parent_id;
        $category->delete();

        $redirectUrl = route('admin.categories.index');
        if ($parentId) {
            $redirectUrl .= '?parent='.$parentId;
        }

        return redirect($redirectUrl)
            ->with('success', 'Category deleted successfully.');
    }
}
