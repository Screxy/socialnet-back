<?php

declare(strict_types=1);

namespace App\Controller;

use App\Db;
use App\Helper\ArrayValidator;
use App\Helper\RequestValidator;
use App\Model\Post;
use App\Model\PostLike;
use App\Model\PostWithLike;
use App\Model\User;
use App\Repository\PostLikeRepository;
use App\Service\UserService;
use Core\NotFoundResponse;
use Core\Request;
use Core\Response;
use DomainException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
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
            RequestValidator::validateAuth($request);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
            return new Response(401);
        }
        try {
            $body = $request->getBody();
            ArrayValidator::validateKeysOnEmpty(['title', 'text'], $body);

            /** @var User $user */
            $user = $request->getCustomParamsByKey('user');

            $post = new Post();
            $post->setAuthorId($user->getId());
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
        try {
            RequestValidator::validateAuth($request);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
            return new Response(401);
        }
        /** @var User $user */
        $user = $request->getCustomParamsByKey('user');
        $posts = PostWithLike::query(
            'SELECT posts.*,
            (IF(post_likes.user_id = :pl_user_id, 1, 0)) AS liked
            FROM posts
            LEFT JOIN post_likes ON posts.id = post_likes.post_id AND post_likes.user_id = :pl_user_id',
            [':pl_user_id' => $user->getId()]
        );
        $response = [];
        if ($posts === null) {
            return NotFoundResponse::create();
        }
        foreach ($posts as $post) {
            $response[] = $post->toArray();
        }

        return new Response(200, $response);
    }

    public function getAllByUser(Request $request): Response
    {
        try {
            RequestValidator::validateAuth($request);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
            return new Response(401);
        }
        /** @var User $user */
        $user = $request->getCustomParamsByKey('user');
        $posts = PostWithLike::query(
            'SELECT posts.*,
            (IF(post_likes.user_id = :pl_user_id, 1, 0)) AS liked
            FROM posts
            LEFT JOIN post_likes ON posts.id = post_likes.post_id AND post_likes.user_id = :pl_user_id where posts.author_id = :pl_user_id',
            [':pl_user_id' => $user->getId()]
        );
        if ($posts === null) {
            return NotFoundResponse::create();
        }

        $response = [];
        foreach ($posts as $post) {
            $response[] = $post->toArray();
        }
        $this->logger->info('response', $response);
        return new Response(200, $response);
    }


    public function getOne(Request $request): Response
    {
        try {
            RequestValidator::validateAuth($request);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
            return new Response(401);
        }
        $id = $request->getCustomParamsByKey('id');
        $post = Post::getById((int)$id);
        if ($post === null) {
            return NotFoundResponse::create();
        }

        return new Response(200, $post->toArray());
    }

    public function setLike(Request $request): Response
    {
        try {
            RequestValidator::validateAuth($request);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
            return new Response(401);
        }
        $id = $request->getCustomParamsByKey('id');
        $like = $request->getBody()['like'];

        $post = Post::getById((int)$id);

        /** @var User $user */
        $user = $request->getCustomParamsByKey('user');

        if ($post === null) {
            return NotFoundResponse::create();
        }

        if ($like) {
            $postLike = PostLikeRepository::getOneByUserIdAndPostId($user->getId(), $post->getId());
            if (!$postLike) {
                $postLike = new PostLike();
                $postLike->setPostId($post->getId());
                $postLike->setUserId($user->getId());
                $postLike->save();
            }
        } else {
            $postLikes = PostLikeRepository::getOneByUserIdAndPostId($user->getId(), $post->getId());
            $postLikes?->destroy();
        }

        return new Response(200);
    }
}
