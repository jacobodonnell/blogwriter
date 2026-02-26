<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Storage::fake('public');
    Storage::fake('private');
});

// --- Store validation ---

it('store rejects missing image_file', function (): void {
    $this->post(route('admin.photos.store'), [
        'alt_text' => 'Test',
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors('image_file');
});

it('store rejects non-image file', function (): void {
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->post(route('admin.photos.store'), [
        'image_file' => $file,
        'alt_text' => 'Test',
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors('image_file');
});

it('store rejects missing alt_text', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $this->post(route('admin.photos.store'), [
        'image_file' => $file,
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors('alt_text');
});

it('store rejects missing status', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $this->post(route('admin.photos.store'), [
        'image_file' => $file,
        'alt_text' => 'Test',
    ])->assertSessionHasErrors('status');
});

it('store rejects invalid status value', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $this->post(route('admin.photos.store'), [
        'image_file' => $file,
        'alt_text' => 'Test',
        'status' => 'archived',
    ])->assertSessionHasErrors('status');
});

// --- Update validation ---

it('update succeeds without replacing image', function (): void {
    $photo = Photo::factory()->published()->create();

    $this->put(route('admin.photos.update', $photo), [
        'alt_text' => 'Updated alt',
        'status' => Status::Published->value,
    ])->assertSessionHasNoErrors();
});

it('update rejects non-image file when replacing', function (): void {
    $photo = Photo::factory()->published()->create();
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->put(route('admin.photos.update', $photo), [
        'image_file' => $file,
        'alt_text' => 'Updated alt',
        'status' => Status::Published->value,
    ])->assertSessionHasErrors('image_file');
});

// --- Max-length rejections ---

it('rejects values over max length', function (string $field, int $maxLength): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $this->post(route('admin.photos.store'), [
        'image_file' => $file,
        'alt_text' => $field === 'alt_text' ? str_repeat('a', $maxLength + 1) : 'Valid alt',
        'caption' => $field === 'caption' ? str_repeat('a', $maxLength + 1) : null,
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors($field);
})->with([
    'alt_text over 500' => ['alt_text', 500],
    'caption over 5000' => ['caption', 5000],
]);

// --- Relationship validation ---

it('rejects non-existent category_id', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $this->post(route('admin.photos.store'), [
        'image_file' => $file,
        'alt_text' => 'Test',
        'status' => Status::Draft->value,
        'category_id' => 99999,
    ])->assertSessionHasErrors('category_id');
});

it('rejects invalid date format for taken_at', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $this->post(route('admin.photos.store'), [
        'image_file' => $file,
        'alt_text' => 'Test',
        'status' => Status::Draft->value,
        'taken_at' => 'not-a-date',
    ])->assertSessionHasErrors('taken_at');
});
