<?php

declare(strict_types=1);

return [
    'enabled' => env('DEMO_MODE', false),
    'credentials' => [
        'email' => env('DEMO_EMAIL', 'demo@blogwriter.dev'),
        'password' => env('DEMO_PASSWORD', 'BlogWriter-Demo-2026!'),
    ],
];
