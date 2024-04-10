<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class UserAlreadyExists extends Exception
{
    public static function create(): static
    {
        return new static('User already exists', 409);
    }
}
