<?php

declare(strict_types=1);

use App\Controller\PostController;
use App\Controller\UserController;

use App\Helper\RequestValidator;
use Core\Request;
use Core\Response;
use Core\Router;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require dirname(__DIR__) . '/vendor/autoload.php';

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, DELETE");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
    }
    exit(0);
}

$logger = new Logger('logger');
$stream_handler = new StreamHandler('php://stdout');
$logger->pushHandler($stream_handler);

$router = new Router();

$router->get('/feed', [UserController::class, 'feed']);
$router->get('/logout', [UserController::class, 'logout']);
$router->post('/authorize', [UserController::class, 'authorize']);
$router->post('/register', [UserController::class, 'register']);

$router->post('/post', [PostController::class, 'store']);
$router->post('/post/:id/setLike', [PostController::class, 'setLike']);
$router->get('/post', [PostController::class, 'getAll']);
$router->get('/post/my', [PostController::class, 'getAllByUser']);
$router->get('/post/:id', [PostController::class, 'getOne']);
$router->get('/test', [PostController::class, 'test']);

$request = new Request($_SERVER);

$router->addNotFoundHandler(function () {
    echo new Response(404, ['message' => 'Not found handler for this response']);
});

try {
    $router->run($request, $logger);
} catch (\Throwable $exception) {
    http_response_code(500);
    echo 'Internal Server Error';
    $logger->critical($exception->getMessage(), $exception->getTrace());
}
