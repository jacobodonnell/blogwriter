<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePhotoRequest;
use App\Http\Requests\Admin\UpdatePhotoRequest;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPhotoController extends Controller
{
    /**
     * Display a listing of photos.
     */
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
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
    public function create(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('admin.photos.create');
    }

    /**
     * Store a newly created photo.
     */
    public function store(StorePhotoRequest $request)
    {
        $data = $request->validated();

        // Generate unique slug from filename
        $filename = $request->file('image_file')->getClientOriginalName();
        $slug = Str::slug(pathinfo($filename, PATHINFO_FILENAME));

        $originalSlug = $slug;
        $counter = 1;
        while (Photo::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter++;
        }

        // Extract EXIF metadata
        $exif = $this->extractExif($request->file('image_file'));

        // Create photo
        $photo = Photo::create([
            'filename' => $filename,
            'slug' => $slug,
            'caption' => $data['caption'] ?? null,
            'alt_text' => $data['alt_text'],
            'status' => $data['status'],
            'published_at' => $data['status'] === 'published' ? now() : null,
            'taken_at' => $data['taken_at'] ?? null,
            'meta' => $exif,
        ]);

        // Add file to MediaLibrary
        try {
            $disk = $photo->status->isPublic() ? 'public' : 'private';

            $photo->addMedia($request->file('image_file'))
                ->toMediaCollection('image', $disk);
        } catch (\Exception $e) {
            \Log::error('Failed to attach photo to MediaLibrary', [
                'photo_id' => $photo->id,
                'error' => $e->getMessage(),
            ]);

            // Delete photo if media attachment fails
            $photo->delete();

            return redirect()->back()
                ->withInput()
                ->withErrors(['image_file' => 'Failed to upload image. Please try again.']);
        }

        return redirect()->route('admin.photos.edit', $photo)
            ->with('success', 'Photo created successfully.');
    }

    /**
     * Show the form for editing the specified photo.
     */
    public function edit(Photo $photo): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
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
        if ($data['status'] === 'published' && $photo->published_at === null) {
            $photo->published_at = now();
        } elseif ($data['status'] !== 'published') {
            $photo->published_at = null;
        }

        $photo->save();

        // Handle new image upload
        if ($request->hasFile('image_file')) {
            try {
                // Extract new EXIF metadata
                $exif = $this->extractExif($request->file('image_file'));
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
                    'photo' => "Cannot delete photo. It is being used by {$articleCount} article(s). Remove it from articles first.",
                ]);
        }

        try {
            $photo->delete();

            return redirect()->route('admin.photos.index')
                ->with('success', 'Photo deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete photo', [
                'photo_id' => $photo->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['photo' => 'Failed to delete photo. Please try again.']);
        }
    }

    /**
     * Extract EXIF metadata from uploaded file.
     */
    private function extractExif($file): array
    {
        if (! function_exists('exif_read_data')) {
            return [];
        }

        $exif = @exif_read_data($file->getPathname());

        if (! $exif) {
            return [];
        }

        return array_filter([
            'camera_model' => $exif['Model'] ?? null,
            'lens' => $exif['LensModel'] ?? null,
            'iso' => $exif['ISOSpeedRatings'] ?? null,
            'aperture' => $exif['FNumber'] ?? null,
            'shutter_speed' => $exif['ExposureTime'] ?? null,
            'width' => $exif['COMPUTED']['Width'] ?? null,
            'height' => $exif['COMPUTED']['Height'] ?? null,
        ]);
    }
}
