<?php

declare(strict_types=1);

use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('downloads a photo as a zip file', function (): void {
    $photo = Photo::factory()->create(['slug' => 'sunset']);

    $response = $this->get(route('admin.photos.download', $photo));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/zip');
    expect($response->headers->get('Content-Disposition'))->toContain('sunset.zip');
});

it('photo zip contains photos.yaml', function (): void {
    $photo = Photo::factory()->create([
        'slug' => 'sunset',
        'caption' => 'A nice sunset.',
        'alt_text' => 'Sunset photo',
    ]);

    $response = $this->get(route('admin.photos.download', $photo));

    $zipBytes = $response->streamedContent();

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $yamlContent = $za->getFromName('photos.yaml');
    $za->close();
    unlink($tmpFile);

    expect($yamlContent)->not->toBeFalse()
        ->and($yamlContent)->toContain('sunset')
        ->and($yamlContent)->toContain('A nice sunset.');
});

it('photo zip contains image file when photo has media', function (): void {
    Storage::fake('public');

    $photo = Photo::factory()->create(['slug' => 'with-image']);

    $fakeFile = UploadedFile::fake()->image('test-photo.jpg', 100, 100);
    $photo->addMedia($fakeFile->getRealPath())
        ->usingFileName('test-photo.jpg')
        ->toMediaCollection('image', 'public');

    $response = $this->get(route('admin.photos.download', $photo));

    $zipBytes = $response->streamedContent();

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);

    $found = false;
    for ($i = 0; $i < $za->numFiles; $i++) {
        $name = $za->getNameIndex($i);
        if ($name === 'images/with-image.jpg') {
            $found = true;
            break;
        }
    }

    $za->close();
    unlink($tmpFile);

    expect($found)->toBeTrue();
});

it('redirects unauthenticated users from the photo download endpoint', function (): void {
    auth()->logout();

    $photo = Photo::factory()->create();

    $this->get(route('admin.photos.download', $photo))
        ->assertRedirect(route('login'));
});

it('returns 404 for a non-existent photo download', function (): void {
    $this->get(route('admin.photos.download', 99999))
        ->assertNotFound();
});
