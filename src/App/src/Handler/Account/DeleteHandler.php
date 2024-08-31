<?php

declare(strict_types=1);

namespace App\Handler\Account;

use App\Service\UserServiceInterface;
use App\Middleware\UserMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private array $config,
        private EntityManagerInterface $em,
        private UserServiceInterface $userService
    ) {
        $this->config      = $config;
        $this->em          = $em;
        $this->userService = $userService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserMiddleware::class);

        if (! $user) {
            return new JsonResponse([
                'data' => [
                    'unsuccess' => 'No result',
                ],
            ], 404);
        }

        $isRemoved = $this->userService->clearAccount($user);

        if (! $isRemoved) {
            return new JsonResponse([
                'errors' => [
                    'delete' => 'Sikertelen profil törlés!'
                ],
            ], 500);
        }

        return new JsonResponse([
            'message' => 'Sikeres profil törlés!',
        ]);
    }
}
