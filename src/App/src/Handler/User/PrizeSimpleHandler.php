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

final class PrizeSimpleHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserServiceInterface $userService
    ) {
        $this->userService = $userService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserMiddleware::class);

        $isPrize = false;

        try {
            $isPrize = $this->userService->prizeActivateSimple($user);
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Váratlan hiba történt',
            ], 404);
        }

        if ($isPrize) {
            return new JsonResponse([
                'message' => 'Sikeresen feliratkoztál a nyereményjátékra!',
            ]);
        }

        return new JsonResponse([
            'message' => 'Sikeresen leiratkoztál a nyereményjátékról!',
        ]);
    }
}
