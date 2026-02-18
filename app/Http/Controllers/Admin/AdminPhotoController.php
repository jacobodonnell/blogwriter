<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Photos\CreatePhotoFromUploadAction;
use App\Actions\Photos\ExtractExifDataAction;
use App\Actions\UpdatePublishedStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePhotoRequest;
use App\Http\Requests\Admin\UpdatePhotoRequest;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminPhotoController extends Controller
{
    public function __construct(
        private readonly CreatePhotoFromUploadAction $createPhotoFromUpload,
        private readonly UpdatePublishedStatusAction $updatePublishedStatus,
        private readonly ExtractExifDataAction $extractExif,
    ) {}

    /**
     * Display a listing of photos.
     */
    public function index(Request $request): View
    {
        $query = Photo::query()
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $photos = $query->paginate(12)->withQueryString();

        return view('admin.photos.index', [
            'photos' => $photos,
        ]);
    }

    /**
     * Show the form for creating a new photo.
     */
    public function create(): View
    {
        return view('admin.photos.create', [
            'photo' => new Photo,
            'categories' => $this->categoryTree(),
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
        } catch (\Exception $exception) {
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
            'categories' => $this->categoryTree(),
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

        $this->updatePublishedStatus->handle($photo, $data['status']);

        if ($request->hasFile('image_file')) {
            try {
                $exif = $this->extractExif->handle($request->file('image_file'));
                $photo->meta = array_merge($photo->meta ?? [], $exif);
                $photo->filename = $request->file('image_file')->getClientOriginalName();
            } catch (\Exception $e) {
                Log::error('Failed to update photo in MediaLibrary', [
                    'photo_id' => $photo->id,
                    'error' => $e->getMessage(),
                ]);

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['image_file' => 'Failed to upload new image. Please try again.']);
            }
        }

        $photo->save();

        if ($request->hasFile('image_file')) {
            $disk = $photo->status->isPublic() ? 'public' : 'private';

            $photo->addMedia($request->file('image_file'))
                ->toMediaCollection('image', $disk);
        }

        return redirect()->route('admin.photos.edit', $photo)
            ->with('success', 'Photo updated successfully.');
    }

    /**
     * Remove the specified photo.
     */
    public function destroy(Photo $photo): RedirectResponse
    {
        if ($photo->articles()->exists()) {
            $articleCount = $photo->articles()->count();

            return redirect()->back()
                ->withErrors([
                    'photo' => sprintf('Cannot delete photo. It is being used by %d article(s). Remove it from articles first.', $articleCount),
                ]);
        }

        try {
            $photo->delete();

            return redirect()->route('admin.photos.index')
                ->with('success', 'Photo deleted successfully.');
        } catch (\Exception $exception) {
            Log::error('Failed to delete photo', [
                'photo_id' => $photo->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['photo' => 'Failed to delete photo. Please try again.']);
        }
    }

    /**
     * @return Collection<int, Category>
     */
    private function categoryTree(): Collection
    {
        return Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
    }
}
