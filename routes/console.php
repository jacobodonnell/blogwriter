<?php

declare(strict_types=1);

use App\Support\DemoSchedule;
use Illuminate\Support\Facades\Schedule;

Schedule::command('demo:reset')
    ->cron(DemoSchedule::cronExpression())
    ->when(fn (): bool => config('demo.enabled'));
