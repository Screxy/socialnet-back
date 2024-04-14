<?php

declare(strict_types=1);

namespace App\Helper;

use App\Exception\Unauthorized;
use App\Repository\UserRepository;
use App\Service\UserService;
use Core\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RequestValidator
{
//    public static function validate(array $data, array $rules): void
//    {
//        foreach ($data as $key => $value) {
//
//        }
//    }

    /**
     * Проверяет авторизацию запроса, и кладет в свойства запроса объект пользователя
     * @param Request $request
     * @return void
     * @throws Unauthorized
     */
    public static function validateAuth(Request $request): void
    {
        $key = (string)getenv('APP_KEY');
        $authorizationHeader = $request->getHeaders()['Authorization'] ?? '';
        $accessToken = str_replace('Bearer ', '', $authorizationHeader);
        $payload = (array)JWT::decode($accessToken, new Key($key, 'HS256'));
        $user = UserRepository::getById($payload['user_id']);
        if ($user === null || $user->getAccessToken() !== $accessToken) {
            throw Unauthorized::create();
        }
        $request->setCustomParams(['user'=>$user]);
    }
}
