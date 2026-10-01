<?php

namespace App\Exceptions;

use RuntimeException;

class DuplicateCinException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('CIN_ALREADY_EXISTS');
    }
}
