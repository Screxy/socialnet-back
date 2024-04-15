<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class Unauthorized extends Exception
{
    public static function create(): static
    {
        return new static('Unauthorized', 401);
    }
}
