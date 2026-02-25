<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleImportRequest;
use App\Services\ArticleImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ArticleImportController extends Controller
{
    public function store(ArticleImportRequest $request, ArticleImportService $importService): JsonResponse
    {
        $parsed = $importService->parseZip($request->file('file'));

        if ($parsed->categoriesYaml !== null) {
            $importService->importCategories($parsed->categoriesYaml);
        }

        $preflight = $importService->preflightCategories($parsed);

        if (! $preflight->ok) {
            $token = Str::random(32);
            session()->put('import_token_'.$token, $parsed);

            return response()->json([
                'status' => 'preflight_warning',
                'missing' => $preflight->missingCategorySlugs,
                'token' => $token,
            ]);
        }

        if ($parsed->settingsYaml !== null) {
            $importService->importSettings($parsed->settingsYaml);
        }

        $photosImported = $importService->importPhotos($parsed, $request->input('duplicate_strategy'), auth()->id());
        $result = $importService->import($parsed, $request->input('duplicate_strategy'), auth()->id());

        return response()->json([
            'status' => 'ok',
            'imported' => $result->imported,
            'skipped' => $result->skipped,
            'photos_imported' => $photosImported,
        ]);
    }

    public function confirm(Request $request, ArticleImportService $importService): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'duplicate_strategy' => ['required', 'in:skip,overwrite'],
        ]);

        $token = $request->input('token');
        $sessionKey = 'import_token_'.$token;
        $parsed = session()->get($sessionKey);

        if ($parsed === null) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired import token.'], 422);
        }

        session()->forget($sessionKey);

        if ($parsed->settingsYaml !== null) {
            $importService->importSettings($parsed->settingsYaml);
        }

        $photosImported = $importService->importPhotos($parsed, $request->input('duplicate_strategy'), auth()->id());
        $result = $importService->import($parsed, $request->input('duplicate_strategy'), auth()->id());

        return response()->json([
            'status' => 'ok',
            'imported' => $result->imported,
            'skipped' => $result->skipped,
            'photos_imported' => $photosImported,
        ]);
    }
}
