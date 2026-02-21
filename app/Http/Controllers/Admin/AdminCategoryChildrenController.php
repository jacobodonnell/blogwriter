<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminCategoryChildrenController extends Controller
{
    public function index(Request $request, string $path): View
    {
        $parent = $this->resolveSlugPath($path);

        $categories = Category::query()
            ->where('parent_id', $parent->id)
            ->withCount(['articles', 'photos', 'children'])
            ->orderBy('name')
            ->get();

        $breadcrumbs = $parent->ancestors()->push($parent);
        $slugPrefix = $breadcrumbs->pluck('slug')->implode('/');

        $allCategories = Category::flatTree();

        $viewData = [
            'categories' => $categories,
            'parent' => $parent,
            'breadcrumbs' => $breadcrumbs,
            'slugPrefix' => $slugPrefix,
            'allCategories' => $allCategories,
            'perPage' => 20,
        ];

        if ($request->header('X-Alpine-Target')) {
            return view('admin.categories._table', $viewData);
        }

        return view('admin.categories.index', $viewData);
    }

    private function resolveSlugPath(string $path): Category
    {
        $segments = explode('/', $path);
        $parentId = null;
        $category = null;

        foreach ($segments as $segment) {
            $category = Category::where('slug', $segment)
                ->where('parent_id', $parentId)
                ->firstOrFail();

            $parentId = $category->id;
        }

        return $category;
    }
}
