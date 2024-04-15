<?php

declare(strict_types=1);

namespace App\Helper;

use App\Exception\Unauthorized;
use App\Repository\UserRepository;
use Core\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RequestValidator
{
    public static function validate(array $data, array $rules): true|array
    {
        $errors = [];
        foreach ($rules as $key => $value) {
            $field = $data[$key];
            $rules = explode('|', $value);
            foreach ($rules as $rule) {
                $isValid = self::validateField($field, $rule);
                if ($isValid !== true) {
                    $errors[$key] = $isValid;
                }
            }
        }
        return $errors ?: true;
    }


    private static function validateField(mixed $value, string $rule): true|string
    {
        if (preg_match('/\b(?:min|max):\d+/', $rule)) {
            $minmaxRule = explode(':', $rule);
            return self::validateLength($minmaxRule[0], (int)$minmaxRule[1], $value) ?: 'Invalid length';
        }
        return match ($rule) {
            'int' => is_int($value) ? true : 'Field must be an integer',
            'string' => is_string($value) ? true : 'Field must be a string',
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) ? true : 'Invalid email',
            default => 'Invalid validation rule',
        };
    }

    private static function validateLength(string $rule, int $length, string $value): bool
    {
        if ($rule === 'min') {
            return strlen($value) >= $length;
        } else {
            return strlen($value) <= $length;
        }
    }

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
        $request->setCustomParams(['user' => $user]);
    }
}
