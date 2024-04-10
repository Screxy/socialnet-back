<?php

declare(strict_types=1);

namespace Core;

use Psr\Log\LoggerInterface;

class Router
{
    private array $handlers;
    /**
     * @var callable
     */
    private $notFoundHandler;

    public function get(string $path, array $method): void
    {
        $this->handlers[] = Handler::create(Request::METHOD_GET, $path, $method[0], $method[1]);
    }

    public function post(string $path, array $method): void
    {
        $this->handlers[] = Handler::create(Request::METHOD_POST, $path, $method[0], $method[1]);
    }

    public function run(Request $request, LoggerInterface $logger): void
    {

        $requestPath = $request->getPath();
        $method = $request->getMethod();
        $callback = null;
        $params = [];

        /** @var Handler $handler */
        foreach ($this->handlers as $handler) {
            $pattern = $this->preparePathPattern($handler->getPath());
            if (preg_match($pattern, $requestPath, $matches) && $method === $handler->getMethod()) {
                $callback = $handler->getCallback();
                array_shift($matches);
                if (!empty($matches)) {
                    $params = $matches;
                }
                break;
            }
        }

        if (null === $callback) {
            $callback = $this->notFoundHandler;
        }

        if (is_array($callback)) {
            $class = $callback[0];
            $handler = new $class($logger);
            $method = $callback[1];
            $callback = [$handler, $method];
        }

        /** @var Response $response */
        $response = call_user_func_array($callback, [$request]);

        echo $response;
    }

    private function preparePathPattern(string $path): string
    {
        $pattern = preg_replace('#/:\w+#', '/([^/]+)', $path);
        $pattern = str_replace('/', '\/', $pattern);
        return '/^' . $pattern . '\/?$/';
    }


    public function addNotFoundHandler(callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }
}
