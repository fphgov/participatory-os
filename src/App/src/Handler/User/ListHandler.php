<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Service\UserServiceInterface;
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
        $users = $this->userService->getRepository()->findAll();

        $normalizedUsers = [];
        foreach ($users as $user) {
            $normalizedUsers[] = $user->normalizer(null, ['groups' => 'list']);
        }

        return new JsonResponse([
            'data' => $normalizedUsers,
        ]);
    }
}
