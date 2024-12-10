<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Service\UserServiceInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ActivateHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserServiceInterface $userService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->userService->activate($request->getAttribute('hash'));
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Fiókja már aktiválva van vagy ismeretlen aktiváló kulcs.',
            ], 404);
        }

        return new JsonResponse([
            'message' => 'Sikeres aktiválás',
        ]);
    }
}
