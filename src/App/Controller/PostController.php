<?php

declare(strict_types=1);

namespace App\Controller;

use App\Db;
use App\Helper\ArrayValidator;
use App\Model\Post;
use App\Model\PostLike;
use App\Model\PostWithLike;
use App\Repository\PostLikeRepository;
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
        $authorizationHeader = $request->getHeaders()['Authorization'] ?? '';
        $accessToken = str_replace('Bearer ', '', $authorizationHeader);
        $user = UserService::getByAccessToken($accessToken);
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
        $id = $request->getCustomParamsByKey('id');
        $like = $request->getBody()['like'];

        $post = Post::getById((int)$id);

        $authorizationHeader = $request->getHeaders()['Authorization'] ?? '';
        $accessToken = str_replace('Bearer ', '', $authorizationHeader);
        $user = UserService::getByAccessToken($accessToken);

        if ($post === null) {
            return NotFoundResponse::create();
        }
        if ($user === null) {
            return NotFoundResponse::create();
        }

        if ($like) {
            $postLike = new PostLike();
            $postLike->setPostId($post->getId());
            $postLike->setUserId($user->getId());
            $postLike->save();
        } else {
            $postLikes = PostLikeRepository::getByUserId($user->getId());
            foreach ($postLikes as $postLike) {
                if ($postLike->getPostId() === $post->getId()) {
                    $postLike->destroy();
                    break;
                }
            }
        }

        return new Response(200);
    }

    public function test(): void
    {
////        $str = 'posts.user_id';
////        $res = lcfirst(str_replace('.', '', ucwords($str, '.')));
////        $res2 = lcfirst(str_replace('_', '', ucwords($res, '_')));
////        var_dump($res2);
//
//        var_dump($res);
    }
}
