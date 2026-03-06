<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleImportConfirmRequest;
use App\Http\Requests\ArticleImportRequest;
use App\Services\ArticleImportService;
use App\Support\ParsedImport;
use Illuminate\Http\JsonResponse;
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

        return $this->performImport($parsed, $request->input('duplicate_strategy'), $request->user()->id, $importService);
    }

    public function confirm(ArticleImportConfirmRequest $request, ArticleImportService $importService): JsonResponse
    {
        $token = $request->input('token');
        $sessionKey = 'import_token_'.$token;
        $parsed = session()->get($sessionKey);

        if ($parsed === null) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired import token.'], 422);
        }

        session()->forget($sessionKey);

        return $this->performImport($parsed, $request->input('duplicate_strategy'), $request->user()->id, $importService);
    }

    private function performImport(
        ParsedImport $parsed,
        string $duplicateStrategy,
        int $userId,
        ArticleImportService $importService,
    ): JsonResponse {
        if ($parsed->settingsYaml !== null) {
            $importService->importSettings($parsed->settingsYaml);
        }

        $photosImported = $importService->importPhotos($parsed, $duplicateStrategy, $userId);
        $result = $importService->import($parsed, $duplicateStrategy, $userId);

        $importService->importRevisions($parsed, $duplicateStrategy, $result->importedSlugs);

        return response()->json([
            'status' => 'ok',
            'imported' => $result->imported,
            'skipped' => $result->skipped,
            'photos_imported' => $photosImported,
            'errors' => $result->errors,
        ]);
    }
}
