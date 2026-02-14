<?php

// Frontend routes
require __DIR__.'/frontend.php';

// Admin routes
require __DIR__.'/admin.php';

// Installation route (feature-flagged, off by default)
require __DIR__.'/install.php';
