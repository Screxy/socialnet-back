<?php

declare(strict_types=1);

namespace Core;

readonly class Response
{
    public function __construct(private int $statusCode, private ?array $data = [])
    {
    }

    public function __toString(): string
    {
        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        return $this->data ? json_encode($this->data) : '';
    }
}
