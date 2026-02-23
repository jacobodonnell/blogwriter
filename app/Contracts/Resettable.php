<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Console\Command;

/**
 * Contract for the application reset service.
 *
 * This interface exists primarily to enable mocking in tests. ResetService is
 * final (enforced by Pint), which prevents Mockery from mocking it directly.
 * UninstallCommand depends on this interface so tests can swap in a mock
 * without triggering a real database wipe. Do not remove it.
 */
interface Resettable
{
    public function reset(Command $command): int;
}
