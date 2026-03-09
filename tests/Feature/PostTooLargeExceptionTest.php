<?php

declare(strict_types=1);

use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\Route;

it('redirects back with error on PostTooLargeException', function (): void {
    Route::post('/test-post-too-large', fn () => throw new PostTooLargeException);

    $this->from('/previous-page')
        ->post('/test-post-too-large')
        ->assertRedirect('/previous-page')
        ->assertSessionHas('error', 'The uploaded file is too large. Please check your server\'s upload size limit.');
});
