<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\PasswordStrength;
use App\Exception\UserAlreadyExists;
use App\Exception\WeakPassword;
use App\Helper\ArrayValidator;
use App\Model\User;
use Core\NotFoundResponse;
use Core\Request;
use Core\Response;
use DomainException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use ZxcvbnPhp\Zxcvbn;

readonly class UserController
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function authorize(Request $request): Response
    {
        try {
            $body = $request->getBody();

            $this->validateBody($body);

            $user = User::where('email', '=', $body['email']);
            if ($user === null) {
                throw new InvalidArgumentException('User not found', 404);
            }

            $isValid = password_verify($body['password'], $user->getPassword());

            if (!$isValid) {
                throw new InvalidArgumentException('Wrong password', 401);
            }
            $key = (string)getenv('APP_KEY');

            $payload = [
                'user_id' => $user->getId(),
                'exp' => time() + 86400,
            ];

            $jwt = JWT::encode($payload, $key, 'HS256');

            $user->setAccessToken($jwt);
            $user->save();

            return new Response(200, ['access_token' => $jwt]);

        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());

            return new Response($exception->getCode(), ['message' => $exception->getMessage()]);
        }
    }

    public function register(Request $request): Response
    {
        try {
            $body = $request->getBody();

            $this->validateBody($body);

            if (User::where('email', '=', $body['email'])) {
                throw UserAlreadyExists::create();
            }

            $userData = [
                $body['email'],
            ];

            $zxcvbn = new Zxcvbn();

            $weak = $zxcvbn->passwordStrength($body['password'], $userData);
            $passwordCheckStatus = match ($weak['score']) {
                2 => PasswordStrength::GOOD,
                3, 4 => PasswordStrength::PERFECT,
                default => PasswordStrength::BAD,
            };

            if ($passwordCheckStatus === PasswordStrength::BAD) {
                throw WeakPassword::create();
            }

            $user = new User();
            $user->setEmail($body['email']);
            $user->setPassword($body['password']);
            $user->save();

            $response = [
                'user_id' => $user->getId(),
                'password' => $passwordCheckStatus,
            ];

            return new Response(201, $response);
        } catch (UserAlreadyExists|WeakPassword|InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());

            return new Response($exception->getCode(), ['message' => $exception->getMessage()]);
        }
    }

    public function feed(Request $request): Response
    {
        try {
            $authorizationHeader = $request->getHeaders()['Authorization'] ?? '';
            $accessToken = str_replace('Bearer ', '', $authorizationHeader);

            $key = (string)getenv('APP_KEY');

            $payload = (array)JWT::decode($accessToken, new Key($key, 'HS256'));

            $user = User::getById($payload['user_id']);

            if ($user->getAccessToken() !== $accessToken) {
                throw new InvalidArgumentException('Wrong token', 401);
            };

            return new Response(200);
        } catch (ExpiredException $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());

            return new Response(401, ['message' => $exception->getMessage()]);
        } catch (DomainException|SignatureInvalidException|InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());

            return new Response(401, ['message' => 'Wrong token']);
        }
    }

    /**
     * @param array $body
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateBody(array $body): void
    {
        ArrayValidator::validateKeysOnEmpty(['email', 'password'], $body);

        $email = (filter_var($body['email'], FILTER_VALIDATE_EMAIL)) ? $body['email'] : '';
        $password = is_string($body['password']) ? $body['password'] : '';

        if (!$email || !$password) {
            throw new InvalidArgumentException('Invalid email or password', 400);
        }

    }
}
