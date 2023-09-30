<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Middleware\UserMiddleware;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class GetVoteHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserMiddleware::class);

        $votes = $user->getVoteCollection()->getValues();

        $normalizedProjects = [];

        foreach ($votes as $vote) {
            $normalizedProjects[] = $vote->getProject()->normalizer(null, ['groups' => 'list']);
        }

        return new JsonResponse([
            'data' => $normalizedProjects,
        ]);
    }
}
