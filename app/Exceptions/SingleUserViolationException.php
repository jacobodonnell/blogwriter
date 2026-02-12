<?php

namespace App\Exceptions;

class SingleUserViolationException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('BlogWriter supports only one user. Use blogwriter:user:create to replace the existing user.');
    }
}
