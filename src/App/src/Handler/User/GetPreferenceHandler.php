<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Middleware\UserMiddleware;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class GetPreferenceHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserMiddleware::class);

        $normalizedUserPreference = $user->getUserPreference()->normalizer(null, [
            'groups' => 'profile'
        ]);

        return new JsonResponse([
            'data' => $normalizedUserPreference,
        ]);
    }
}
