<?php

declare(strict_types=1);

// Frontend routes
require __DIR__.'/frontend.php';

// Admin routes
require __DIR__.'/admin.php';

// Installation route (feature-flagged, off by default)
require __DIR__.'/install.php';

// Catch-all 404 — must be last so it doesn't shadow any routes
Route::fallback(function () {
    return response()->view('errors.404', status: 404);
});
