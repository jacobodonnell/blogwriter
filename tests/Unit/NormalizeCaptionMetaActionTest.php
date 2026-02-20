<?php

declare(strict_types=1);

use App\Actions\NormalizeCaptionMetaAction;

beforeEach(function (): void {
    $this->action = new NormalizeCaptionMetaAction;
});

it('removes featured_image_caption when use_photo_caption is set', function (): void {
    $meta = [
        'use_photo_caption' => true,
        'featured_image_caption' => 'Custom caption',
    ];

    $result = $this->action->handle($meta, 1);

    expect($result)
        ->toHaveKey('use_photo_caption')
        ->not->toHaveKey('featured_image_caption');
});

it('removes use_photo_caption when it is not set', function (): void {
    $meta = [
        'featured_image_caption' => 'Custom caption',
        'use_photo_caption' => null,
    ];

    $result = $this->action->handle($meta, 1);

    expect($result)
        ->toHaveKey('featured_image_caption')
        ->not->toHaveKey('use_photo_caption');
});

it('clears all caption keys when no featured image exists', function (): void {
    $meta = [
        'featured_image_caption' => 'Custom caption',
        'use_photo_caption' => true,
    ];

    $result = $this->action->handle($meta, null, null);

    expect($result)
        ->not->toHaveKey('featured_image_caption')
        ->not->toHaveKey('use_photo_caption');
});

it('clears all caption keys when photo_id is zero and no URL', function (): void {
    $meta = [
        'featured_image_caption' => 'Custom caption',
    ];

    $result = $this->action->handle($meta, 0, '');

    expect($result)
        ->not->toHaveKey('featured_image_caption')
        ->not->toHaveKey('use_photo_caption');
});

it('preserves other meta keys', function (): void {
    $meta = [
        'meta_title' => 'My Title',
        'featured_image_caption' => 'Caption',
    ];

    $result = $this->action->handle($meta, 1);

    expect($result)->toHaveKey('meta_title', 'My Title')
        ->toHaveKey('featured_image_caption', 'Caption');
});
