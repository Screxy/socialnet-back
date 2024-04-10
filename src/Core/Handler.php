<?php

declare(strict_types=1);

namespace Core;

class Handler
{
    public array $callback;

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly string $controller,
        private readonly string $action
    )
    {
        $this->callback = [$this->controller, $this->action];
    }

    public static function create(string $method, string $path, string $controller, string $action): self
    {
        return new self ($method, $path, $controller, $action);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getCallback(): array
    {
        return $this->callback;
    }
}
