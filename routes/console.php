<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

$interval = (int) config('demo.reset_interval', 30);

Schedule::command('demo:reset')
    ->cron("*/{$interval} * * * *")
    ->when(fn (): bool => config('demo.enabled'));
