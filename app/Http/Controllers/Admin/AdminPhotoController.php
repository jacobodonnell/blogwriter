<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Photos\CreatePhotoFromUploadAction;
use App\Actions\Photos\ExtractExifDataAction;
use App\Actions\UpdatePublishedStatusAction;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePhotoRequest;
use App\Http\Requests\Admin\UpdatePhotoRequest;
use App\Models\Photo;
use Illuminate\Http\Request;

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
    public function index(Request $request): \Illuminate\View\View
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
    public function create(): \Illuminate\View\View
    {
        return view('admin.photos.create');
    }

    /**
     * Store a newly created photo.
     */
    public function store(StorePhotoRequest $request)
    {
        $data = $request->validated();

        try {
            $photo = $this->createPhotoFromUpload->handle($request->file('image_file'), [
                'slug' => pathinfo($request->file('image_file')->getClientOriginalName(), PATHINFO_FILENAME),
                'alt_text' => $data['alt_text'],
                'caption' => $data['caption'] ?? null,
                'status' => $data['status'],
                'taken_at' => $data['taken_at'] ?? null,
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
            \Log::error('Failed to create photo', [
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
    public function show(Photo $photo): \Illuminate\View\View
    {
        return view('admin.photos.show', [
            'photo' => $photo,
        ]);
    }

    /**
     * Show the form for editing the specified photo.
     */
    public function edit(Photo $photo): \Illuminate\View\View
    {
        return view('admin.photos.edit', [
            'photo' => $photo,
        ]);
    }

    /**
     * Update the specified photo.
     */
    public function update(UpdatePhotoRequest $request, Photo $photo)
    {
        $data = $request->validated();

        // Update photo attributes
        $photo->caption = $data['caption'] ?? null;
        $photo->alt_text = $data['alt_text'];
        $photo->status = $data['status'];
        $photo->taken_at = $data['taken_at'] ?? null;

        // Update published_at based on status
        $this->updatePublishedStatus->handle($photo, $data['status']);

        $photo->save();

        // Handle new image upload
        if ($request->hasFile('image_file')) {
            try {
                // Extract new EXIF metadata
                $exif = $this->extractExif->handle($request->file('image_file'));
                $photo->meta = array_merge($photo->meta ?? [], $exif);

                // Update filename
                $filename = $request->file('image_file')->getClientOriginalName();
                $photo->filename = $filename;
                $photo->save();

                // Determine disk based on status
                $disk = $photo->status->isPublic() ? 'public' : 'private';

                // Replace existing media
                $photo->addMedia($request->file('image_file'))
                    ->toMediaCollection('image', $disk);
            } catch (\Exception $e) {
                \Log::error('Failed to update photo in MediaLibrary', [
                    'photo_id' => $photo->id,
                    'error' => $e->getMessage(),
                ]);

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['image_file' => 'Failed to upload new image. Please try again.']);
            }
        }

        return redirect()->route('admin.photos.edit', $photo)
            ->with('success', 'Photo updated successfully.');
    }

    /**
     * Remove the specified photo.
     */
    public function destroy(Photo $photo)
    {
        // Check if photo is used by any articles
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
            \Log::error('Failed to delete photo', [
                'photo_id' => $photo->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['photo' => 'Failed to delete photo. Please try again.']);
        }
    }
}
