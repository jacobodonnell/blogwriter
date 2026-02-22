<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\View\View;

final class ExportController extends Controller
{
    public function index(): View
    {
        return view('admin.export.index', [
            'articleCount' => Article::query()->count(),
        ]);
    }
}
