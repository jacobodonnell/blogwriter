<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Photos\CreatePhotoFromUploadAction;
use App\Actions\Photos\ExtractExifDataAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePhotoRequest;
use App\Http\Requests\Admin\UpdatePhotoRequest;
use App\Models\Category;
use App\Models\Photo;
use App\Services\ContentFilterService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminPhotoController extends Controller
{
    public function __construct(
        private readonly CreatePhotoFromUploadAction $createPhotoFromUpload,
        private readonly ExtractExifDataAction $extractExif,
        private readonly ContentFilterService $contentFilter,
    ) {}

    /**
     * Display a listing of photos.
     */
    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->input('perPage'), [12, 24, 48]) ? (int) $request->input('perPage') : 12;

        $photos = $this->contentFilter->filterPhotos($request, options: [
            'adminMode' => true,
            'allowedPerPage' => [12, 24, 48],
            'perPage' => 12,
        ]);

        $categories = Category::tree()->get();

        $viewData = [
            'photos' => $photos,
            'categories' => $categories,
            'perPage' => $perPage,
        ];

        if ($request->header('X-Alpine-Target')) {
            return view('admin.photos._grid', $viewData);
        }

        return view('admin.photos.index', $viewData);
    }

    /**
     * Show the form for creating a new photo.
     */
    public function create(): View
    {
        return view('admin.photos.create', [
            'photo' => new Photo(['category_id' => request('category_id')]),
            'categories' => Category::tree()->get(),
        ]);
    }

    /**
     * Store a newly created photo.
     */
    public function store(StorePhotoRequest $request): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        try {
            $photo = $this->createPhotoFromUpload->handle($request->file('image_file'), [
                'slug' => $data['slug'] ?? null,
                'alt_text' => $data['alt_text'],
                'caption' => $data['caption'] ?? null,
                'status' => $data['status'],
                'taken_at' => $data['taken_at'] ?? null,
                'category_id' => $data['category_id'] ?? null,
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'photo' => [
                        'id' => $photo->id,
                        'image_url' => $photo->image_url,
                        'alt_text' => $photo->alt_text,
                    ],
                ]);
            }

            return redirect()->route('admin.photos.edit', $photo)
                ->with('success', 'Photo created successfully.');
        } catch (Exception $exception) {
            Log::error('Failed to create photo', [
                'error' => $exception->getMessage(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Failed to upload image.'], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['image_file' => 'Failed to upload image. Please try again.']);
        }
    }

    /**
     * Display the specified photo.
     */
    public function show(Photo $photo): View
    {
        return view('admin.photos.show', [
            'photo' => $photo,
        ]);
    }

    /**
     * Show the form for editing the specified photo.
     */
    public function edit(Photo $photo): View
    {
        return view('admin.photos.edit', [
            'photo' => $photo,
            'articleCount' => $photo->articles()->count(),
            'categories' => Category::tree()->get(),
        ]);
    }

    /**
     * Update the specified photo.
     */
    public function update(UpdatePhotoRequest $request, Photo $photo): RedirectResponse
    {
        $data = $request->validated();

        $photo->caption = $data['caption'] ?? null;
        $photo->alt_text = $data['alt_text'];
        $photo->status = $data['status'];
        $photo->taken_at = $data['taken_at'] ?? null;
        $photo->category_id = $data['category_id'] ?? null;

        $photo->slug = $data['slug'] ?? $photo->slug;

        if ($request->hasFile('image_file')) {
            try {
                $exif = $this->extractExif->handle($request->file('image_file'));
                $photo->meta = array_merge($photo->meta ?? [], $exif);
                $photo->filename = $request->file('image_file')->getClientOriginalName();
            } catch (Exception $e) {
                Log::error('Failed to update photo in MediaLibrary', [
                    'photo_id' => $photo->id,
                    'error' => $e->getMessage(),
                ]);

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['image_file' => 'Failed to upload new image. Please try again.']);
            }
        }

        DB::transaction(function () use ($request, $photo): void {
            $photo->save();

            if ($request->hasFile('image_file')) {
                $disk = $photo->status->isPublic() ? 'public' : 'private';
                $extension = $request->file('image_file')->getClientOriginalExtension();

                $photo->addMedia($request->file('image_file'))
                    ->usingFileName($photo->slug.'.'.$extension)
                    ->usingName($photo->slug)
                    ->toMediaCollection('image', $disk);
            }
        });

        return redirect()->route('admin.photos.edit', $photo)
            ->with('success', 'Photo updated successfully.');
    }

    /**
     * Remove the specified photo.
     */
    public function destroy(Photo $photo): RedirectResponse
    {
        try {
            $articleCount = $photo->articles()->count();

            DB::transaction(function () use ($photo): void {
                $photo->articles()->update(['photo_id' => null]);
                $photo->delete();
            });

            $message = $articleCount > 0
                ? sprintf('Photo deleted successfully. Removed from %d %s.', $articleCount, Str::plural('article', $articleCount))
                : 'Photo deleted successfully.';

            return redirect()->route('admin.photos.index')
                ->with('success', $message);
        } catch (Exception $exception) {
            Log::error('Failed to delete photo', [
                'photo_id' => $photo->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['photo' => 'Failed to delete photo. Please try again.']);
        }
    }
}
