# Photo Model Implementation - Remaining Tasks

## ✅ Completed
1. ✅ Photo migration created
2. ✅ Articles refactor migration created (removes featured_image columns, adds photo_id)
3. ✅ Photo model with MediaLibrary integration
4. ✅ PhotoObserver for disk migration
5. ✅ PhotoObserver registered in AppServiceProvider
6. ✅ Article model updated (removed MediaLibrary, added featuredPhoto() relationship)
7. ✅ ArticleObserver simplified (removed media logic)
8. ✅ Photo form requests (StorePhotoRequest, UpdatePhotoRequest)
9. ✅ Article form requests updated (added photo_id validation)
10. ✅ PhotoFactory with demo images
11. ✅ AttachesFeaturedImages trait updated to support Photo model

## 🚧 In Progress - Need to Complete

### Controllers
- [ ] AdminPhotoController (CRUD for photos)
- [ ] PhotoController (public routes)
- [ ] ArticleController needs refactoring for Photo creation

### Routes
- [ ] Add admin photo routes
- [ ] Add public photo routes

### Views
- [ ] Admin photo views (index, create, edit, form)
- [ ] Public photo views (index, show)
- [ ] Update article form to include photo picker
- [ ] Update article show view to check Photo status

### Seeders
- [ ] PhotoSeeder
- [ ] Update DemoArticleSeeder to use Photos
- [ ] Update FullArticleSeeder to use Photos
- [ ] Update DatabaseSeeder to include PhotoSeeder

### Tests
- [ ] Refactor all 6 featured image tests to use Photo model
- [ ] Create Photo CRUD tests
- [ ] Create Photo public display tests
- [ ] Create Photo status filtering tests

## Critical Next Steps

1. **Finish AdminPhotoController** - Basic CRUD
2. **Finish ArticleController** - Add photo creation methods
3. **Create PhotoSeeder** - Demo photos
4. **Update routes** - Register photo routes
5. **Run migrate:fresh --seed** - Test everything works

## Photo Creation Flow (3 Methods)

When creating/editing an Article, the controller should:

1. **Upload new file** → Creates Photo with MediaLibrary
   ```php
   if ($request->hasFile('featured_image_file')) {
       $photo = $this->createPhotoFromUpload($request->file('featured_image_file'), $data);
       $data['photo_id'] = $photo->id;
   }
   ```

2. **External URL** → Creates Photo with URL in meta
   ```php
   elseif ($request->filled('featured_image') && filter_var($request->featured_image, FILTER_VALIDATE_URL)) {
       $photo = $this->createPhotoFromUrl($request->featured_image, $data);
       $data['photo_id'] = $photo->id;
   }
   ```

3. **Select existing photo** → Use existing Photo
   ```php
   elseif ($request->filled('photo_id')) {
       $data['photo_id'] = $request->photo_id;
   }
   ```

## Photo Helper Methods Needed in ArticleController

```php
private function createPhotoFromUrl(string $url, array $articleData): Photo
{
    $filename = basename(parse_url($url, PHP_URL_PATH)) ?: 'external-image.jpg';
    $slug = Str::slug(pathinfo($filename, PATHINFO_FILENAME));

    // Ensure unique slug
    $originalSlug = $slug;
    $counter = 1;
    while (Photo::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $counter++;
    }

    return Photo::create([
        'filename' => $filename,
        'slug' => $slug,
        'alt_text' => $articleData['title'] ?? 'Featured image',
        'status' => $articleData['status'] ?? 'draft',
        'meta' => ['external_url' => $url],
    ]);
}

private function createPhotoFromUpload($file, array $articleData): Photo
{
    $filename = $file->getClientOriginalName();
    $slug = Str::slug(pathinfo($filename, PATHINFO_FILENAME));

    // Ensure unique slug
    $originalSlug = $slug;
    $counter = 1;
    while (Photo::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $counter++;
    }

    // Extract EXIF if available
    $exif = $this->extractExif($file);

    $photo = Photo::create([
        'filename' => $filename,
        'slug' => $slug,
        'alt_text' => $articleData['title'] ?? 'Featured image',
        'status' => $articleData['status'] ?? 'draft',
        'meta' => $exif,
    ]);

    // Add file to Photo's media collection
    $disk = $photo->status->isPublic() ? 'public' : 'private';
    $photo->addMedia($file)
        ->toMediaCollection('image', $disk);

    return $photo;
}

private function extractExif($file): array
{
    if (!function_exists('exif_read_data')) {
        return [];
    }

    $exif = @exif_read_data($file->getPathname());

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
```

## Test Migration Plan

Since we're pre-alpha:
1. Run `php artisan migrate:fresh` (will drop all tables)
2. Run seeders
3. Verify Photos and Articles work
4. Old featured_image column is gone
5. Articles use photo_id relationship

## Breaking Changes Summary

- `articles.featured_image` column removed
- `articles.featured_image_*` metadata columns removed
- Article no longer has MediaLibrary (`featured_image` collection)
- Article uses `featuredPhoto()` relationship instead
- Photo owns the MediaLibrary (`image` collection)
- Photo has independent status (draft/published)
