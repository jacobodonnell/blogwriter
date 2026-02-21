<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Console\Command;

interface Resettable
{
    public function reset(Command $command): int;
}
