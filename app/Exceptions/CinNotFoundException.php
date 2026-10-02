<?php

namespace App\Exceptions;

use RuntimeException;

class CinNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('CIN_NOT_FOUND');
    }
}
