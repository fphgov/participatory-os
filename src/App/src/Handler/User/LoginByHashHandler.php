<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Service\UserServiceInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LoginByHashHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserServiceInterface $userService
    ) {
        $this->userService = $userService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $token = null;

        try {
            $token = $this->userService->loginWithHash($request->getAttribute('hash'));
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Sikertelen belépés',
                'token'   => null
            ], 404);
        }

        return new JsonResponse([
            'message' => 'Sikeres belépés',
            'token'   => $token
        ]);
    }
}
