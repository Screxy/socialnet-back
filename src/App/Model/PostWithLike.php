<?php

declare(strict_types=1);

namespace App\Model;

class PostWithLike extends Post
{
    protected int $liked = 0;

    public function getLiked(): int
    {
        return $this->liked;
    }

    public function setLiked(int $liked): void
    {
        $this->liked = $liked;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'author_id' => $this->getAuthorId(),
            'title' => $this->getTitle(),
            'text' => $this->getText(),
            'created_at' => $this->getCreatedAt(),
            'liked' => (boolean)$this->getLiked(),
        ];
    }

}
