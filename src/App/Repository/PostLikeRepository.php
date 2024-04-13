<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\PostLike;

class PostLikeRepository
{
    /**
     * @param int $id
     * @return PostLike[]
     */
    public static function getByUserId(int $id): array
    {
        return PostLike::where('user_id', '=', (string)$id);
    }

    /**
     * @param int $id
     * @return PostLike[]
     */
    public static function getByPostId(int $id): array
    {
        return PostLike::where('post_id', '=', (string)$id);
    }
}
