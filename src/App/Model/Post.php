<?php

declare(strict_types=1);

namespace App\Model;

class Post extends ActiveRecordEntity
{
    protected int $authorId;
    protected string $title;
    protected string $text;
    protected ?string $createdAt = null;

    protected static function getTableName(): string
    {
        return 'posts';
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function setAuthorId(int $authorId): void
    {
        $this->authorId = $authorId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'author_id' => $this->getAuthorId(),
            'title' => $this->getTitle(),
            'text' => $this->getText(),
            'created_at' => $this->getCreatedAt(),
        ];
    }
}
