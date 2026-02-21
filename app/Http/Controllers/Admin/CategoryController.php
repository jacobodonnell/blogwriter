<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

final class CategoryController extends Controller
{
    /**
     * Display a flat listing of all categories.
     */
    public function index(Request $request): View
    {
        $query = Category::query()
            ->with('parent')
            ->withCount(['articles', 'photos', 'children'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('slug', 'like', sprintf('%%%s%%', $search));
            });
        }

        if ($request->filled('content_type')) {
            match ($request->input('content_type')) {
                'articles' => $query->has('articles'),
                'photos' => $query->has('photos'),
                default => null,
            };
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', (int) $request->input('parent_id'));
        }

        $perPage = in_array((int) $request->input('perPage'), [10, 20, 50, 100]) ? (int) $request->input('perPage') : 20;

        $categories = $query->paginate($perPage)->withQueryString();

        $viewData = [
            'categories' => $categories,
            'allCategories' => Category::flatTree(),
            'perPage' => $perPage,
        ];

        if ($request->header('X-Alpine-Target')) {
            return view('admin.categories._table', $viewData);
        }

        return view('admin.categories.index', $viewData);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): Response|RedirectResponse
    {
        $parentId = $request->input('parent_id');
        $parent = $parentId ? Category::find((int) $parentId) : null;
        $isAjax = (bool) $request->header('X-Alpine-Target');

        $categoryRequest = new CategoryRequest;
        $validator = Validator::make($request->all(), $categoryRequest->rules(), $categoryRequest->messages());

        if ($validator->fails()) {
            if ($isAjax) {
                $formHtml = view('admin.categories._add-form', [
                    'parent' => $parent,
                    'allCategories' => Category::flatTree(),
                ])->withErrors($validator)->render();

                return response('<div id="add-category-form">'.$formHtml.'</div>', 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        Category::create($data);

        if ($isAjax) {
            $allCategories = Category::flatTree();

            return response(view('admin.categories._store-success', ['parent' => $parent, 'allCategories' => $allCategories]));
        }

        return redirect()->route('admin.categories.index')
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
        if ($category->children()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with subcategories. Remove subcategories first.');
        }

        if ($category->articles()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with articles. Remove articles first.');
        }

        if ($category->photos()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with photos. Remove photos first.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
