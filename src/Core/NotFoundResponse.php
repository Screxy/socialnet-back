<?php

declare(strict_types=1);

namespace Core;

readonly class NotFoundResponse extends Response
{
    public static function create(): NotFoundResponse
    {
        return new static(404, ['message' => 'Not found']);
    }
}
