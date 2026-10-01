<?php

namespace App\Exceptions;

use RuntimeException;

class DuplicateEmailException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('EMAIL_ALREADY_EXISTS');
    }
}
