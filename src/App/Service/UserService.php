<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\User;
use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserService
{
    public static function getByAccessToken(string $accessToken): ?User
    {
        $key = (string)getenv('APP_KEY');

        $payload = (array)JWT::decode($accessToken, new Key($key, 'HS256'));
        $user = UserRepository::getById($payload['user_id']);
        return $user ?? null;
    }
}
