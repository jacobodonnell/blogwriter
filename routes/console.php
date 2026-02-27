<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('demo:reset')
    ->everyThirtyMinutes()
    ->when(fn (): bool => config('demo.enabled'));
