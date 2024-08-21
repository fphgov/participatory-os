<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Service\UserServiceInterface;
use App\Middleware\UserMiddleware;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NewsletterSimpleHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserServiceInterface $userService
    ) {
        $this->userService = $userService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserMiddleware::class);

        try {
            $this->userService->newsletterActivateSimple($user);
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Váratlan hiba történt',
            ], 404);
        }

        return new JsonResponse([
            'message' => 'Sikeres feliratkozás hírlevélre',
        ]);
    }
}
