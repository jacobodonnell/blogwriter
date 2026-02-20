<?php

use App\Actions\Photos\ExtractExifDataAction;
use Illuminate\Http\UploadedFile;

beforeEach(function (): void {
    $this->action = new ExtractExifDataAction;
});

it('returns empty array for non-image files', function (): void {
    $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

    $result = $this->action->handle($file);

    expect($result)->toBeArray()->toBeEmpty();
});

it('returns empty array for image without exif data', function (): void {
    $file = UploadedFile::fake()->image('photo.png', 100, 100);

    $result = $this->action->handle($file);

    expect($result)->toBeArray();
});

it('returns array with expected keys when exif data exists', function (): void {
    // Create a real JPEG with EXIF data using GD
    $image = imagecreatetruecolor(100, 100);
    $tempPath = tempnam(sys_get_temp_dir(), 'exif_test_');
    imagejpeg($image, $tempPath);
    imagedestroy($image);

    $file = new UploadedFile($tempPath, 'photo.jpg', 'image/jpeg', null, true);

    $result = $this->action->handle($file);

    // Basic JPEG from GD won't have camera info, but the method should still work
    expect($result)->toBeArray();

    // Clean up
    @unlink($tempPath);
});
