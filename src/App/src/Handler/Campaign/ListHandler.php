<?php

declare(strict_types=1);

namespace App\Handler\Campaign;

use App\Service\UserServiceInterface;
use Jwt\Handler\JwtAuthMiddleware;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ListHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserServiceInterface $userService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $token = $request->getAttribute(JwtAuthMiddleware::class);

        $user = $this->userService->getRepository()->findOneBy([
            'email' => $token['user']->email,
        ]);

        return new JsonResponse($user);
    }
}
