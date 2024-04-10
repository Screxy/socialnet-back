<?php

declare(strict_types=1);

namespace App\Model;

use InvalidArgumentException;

class User extends ActiveRecordEntity
{
    protected string $email;
    protected string $password;
    protected ?string $accessToken = null;

    protected static function getTableName(): string
    {
        return 'user';
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
}
