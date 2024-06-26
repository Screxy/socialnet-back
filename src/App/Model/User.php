<?php

declare(strict_types=1);

namespace App\Model;

class User extends ActiveRecordEntity
{
    protected string $username;
    protected string $email;
    protected string $password;
    protected ?string $accessToken = null;
    protected ?string $createdAt = null;

    protected static function getTableName(): string
    {
        return 'users';
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getAccessToken(): string
    {
        return (string)$this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function toArray(): array
    {
        return [
            'id'=> $this->getId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
        ];
    }
}
