<?php

namespace Core;

class Request
{
    public const string METHOD_GET = 'GET';
    public const string METHOD_POST = 'POST';

    private array $headers = [];
    private string $path = '';
    private array $body = [];
    private string $method = '';

    /**
     * @param array $server
     */
    public function __construct(array $server = [])
    {
        $this->headers = getallheaders();
        $this->path = parse_url($server['REQUEST_URI'])['path'];
        $this->body = json_decode(file_get_contents('php://input'), true) ?? [];
        $this->method = $server['REQUEST_METHOD'];
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
