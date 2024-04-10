<?php

declare(strict_types=1);

namespace App\Helper;

use InvalidArgumentException;

class ArrayValidator
{
    public static function validateKeysOnEmpty(array $expectedKeys, array $array): void
    {
        foreach ($expectedKeys as $expectedKey) {
            if (empty($array[$expectedKey])) {
                throw new InvalidArgumentException(sprintf('Empty value for key: %s', $expectedKey), 400);
            }
        }
    }
}
