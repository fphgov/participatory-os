<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Service\UserServiceInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class PrizeHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserServiceInterface $userService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->userService->prizeActivate($request->getAttribute('hash'));
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Jelentkezését a nyereményjátékra már fogadtuk vagy a jelentkezéshez használt kulcs érvénytelen',
            ], 404);
        }

        return new JsonResponse([
            'message' => 'Sikeres aktiválás',
        ]);
    }
}
