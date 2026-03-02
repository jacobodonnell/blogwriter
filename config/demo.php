<?php

declare(strict_types=1);

return [
    'enabled'        => env('DEMO_MODE', false),
    'reset_interval' => (int) env('DEMO_RESET_INTERVAL', 120),
    'credentials'    => [
        'email'    => env('DEMO_EMAIL', 'demo@blogwriter.tech'),
        'password' => env('DEMO_PASSWORD', 'BlogWriter-Demo-2026!'),
    ],
];
