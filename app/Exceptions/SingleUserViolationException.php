<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class SingleUserViolationException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('BlogWriter supports only one user. Use blogwriter:create-user to replace the existing user.');
    }
}
