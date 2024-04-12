<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\ArrayValidator;
use App\Model\Post;
use App\Service\UserService;
use Core\NotFoundResponse;
use Core\Request;
use Core\Response;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

readonly class PostController
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function store(Request $request): Response
    {
        try {
            $body = $request->getBody();
            ArrayValidator::validateKeysOnEmpty(['title', 'text'], $body);

            $authorizationHeader = $request->getHeaders()['Authorization'] ?? '';
            $accessToken = str_replace('Bearer ', '', $authorizationHeader);
            $user = UserService::getByAccessToken($accessToken);

            $post = new Post();
            $post->setUserId($user->getId());
            $post->setTitle($body['title']);
            $post->setText($body['text']);
            $post->setCreatedAt(date("Y-m-d H:i:s"));
            $post->save();

            return new Response(201, $post->toArray());
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());

            return new Response($exception->getCode(), ['message' => $exception->getMessage()]);
        }
    }

    public function getAll(Request $request): Response
    {
        $posts = Post::findAll();
        $response = [];
        if ($posts === null) {
            return NotFoundResponse::create();
        }
        foreach ($posts as $post) {
            $response[] = $post->toArray();
        }

        return new Response(200, $response);
    }

    public function getOne(Request $request): Response
    {
        $id = $request->getCustomParamsByKey('id');
        $post = Post::getById((int)$id);
        if ($post === null) {
            return NotFoundResponse::create();
        }

        return new Response(200, $post->toArray());
    }

    public function setLike(Request $request): Response
    {
        var_dump($request->getAllCustomParams());
        return new Response(200);
    }
}
