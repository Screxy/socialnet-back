<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\PasswordStrength;
use App\Exception\Unauthorized;
use App\Exception\UserAlreadyExists;
use App\Exception\WeakPassword;
use App\Helper\ArrayValidator;
use App\Helper\RequestValidator;
use App\Model\User;
use App\Service\UserService;
use Core\Request;
use Core\Response;
use DomainException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
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

            $valid = RequestValidator::validate($body, [
                'email' => 'email',
                'password' => 'min:6'
            ]);

            if ($valid !== true) {
                return new Response(400, $valid);
            }

            $user = User::where('email', '=', $body['email']);

            if ($user === null) {
                throw new InvalidArgumentException('User not found', 401);
            }
            $user = $user[0];
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

            $valid = RequestValidator::validate($body, [
                'email' => 'email',
                'password' => 'min:6'
            ]);

            if ($valid !== true) {
                return new Response(400, $valid);
            }

            if (User::where('email', '=', $body['email'])) {
                throw UserAlreadyExists::create();
            }
            if (User::where('username', '=', $body['username'])) {
                throw UserAlreadyExists::create();
            }
            $userData = [
                $body['email'],
                $body['username'],
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
            $user->setUsername($body['username']);
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
            RequestValidator::validateAuth($request);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
            return new Response(401);
        }
        try {

            /** @var User $user */
            $user = $request->getCustomParamsByKey('user');

            return new Response(200, $user->toArray());
        } catch (ExpiredException $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());

            return new Response(401, ['message' => $exception->getMessage()]);
        } catch (DomainException|SignatureInvalidException|InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());

            return new Response(401, ['message' => 'Wrong token']);
        }
    }

    public function logout(Request $request): Response
    {
        try {
            $authorizationHeader = $request->getHeaders()['Authorization'] ?? '';
            $accessToken = str_replace('Bearer ', '', $authorizationHeader);

            $user = UserService::getByAccessToken($accessToken);
            if ($user !== null) {
                $user->setAccessToken('');
                $user->save();
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
            return new Response(401);
        }

        return new Response(200);
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
