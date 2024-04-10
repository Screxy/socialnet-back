<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class WeakPassword extends Exception
{
    public static function create(): static
    {
        return new static('Weak password', 403);
    }
}
