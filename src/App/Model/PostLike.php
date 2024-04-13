<?php

declare(strict_types=1);

namespace App\Model;

class PostLike extends ActiveRecordEntity
{
    protected int $userId;
    protected int $postId;
    protected ?string $createdAt = null;

    protected static function getTableName(): string
    {
        return 'post_likes';
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->getUserId(),
            'post_id' => $this->getPostId(),
        ];
    }
}
