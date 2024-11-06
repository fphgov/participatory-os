<?php

declare(strict_types=1);

namespace Jwt\Handler;

use Jwt\Service\TokenServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tuupola\Middleware\JwtAuthentication;

class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly JwtAuthentication $auth,
        private readonly TokenServiceInterface $tokenService
    )
    {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $token = $this->extractToken($request);

        if (!$token || $this->tokenService->isTokenBlacklisted($token)) {
            return new JsonResponse([
                'message' => 'Bejelentkezés szükséges',
            ], 404);
        }

        return $this->auth->process($request, $handler);
    }

    private function extractToken(ServerRequestInterface $request): ?string
    {
        if ($request->hasHeader('Authorization')) {
            $authHeader = $request->getHeaderLine('Authorization');

            if (str_starts_with($authHeader, 'Bearer ')) {
                return substr($authHeader, 7);
            }
        }

        return null;
    }
}
