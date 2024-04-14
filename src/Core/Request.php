<?php

namespace Core;

class Request
{
    public const string METHOD_GET = 'GET';
    public const string METHOD_POST = 'POST';

    private string $method = '';
    private string $path = '';
    private array $headers = [];
    private array $parameters = [];
    private array $body = [];
    private array $customParams = [];

    /**
     * @param array $server
     */
    public function __construct(array $server = [])
    {
        $this->method = $server['REQUEST_METHOD'];
        $this->path = parse_url($server['REQUEST_URI'])['path'];
        $this->headers = getallheaders();
        parse_str($server['QUERY_STRING'] ?? '', $this->parameters);
        $this->body = json_decode(file_get_contents('php://input'), true) ?? [];
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

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getAllCustomParams(): array
    {
        return $this->customParams;
    }

    public function getCustomParamsByKey(string $key): mixed
    {
        if (isset($this->customParams[$key])) {
            return $this->customParams[$key];
        }
        return '';
    }

    public function setCustomParams(array $customParams): void
    {
        foreach ($customParams as $key => $customParam) {
            $this->customParams[$key] = $customParam;
        }
    }
}
