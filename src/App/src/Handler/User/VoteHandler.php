<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Middleware\UserMiddleware;
use App\Service\VoteMessageServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class VoteHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private VoteMessageServiceInterface $voteMessageService,
    ) {
        $this->em                 = $em;
        $this->voteMessageService = $voteMessageService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserMiddleware::class);
        $body = $request->getParsedBody();

        return $this->voteMessageService->votingWithJsonMessage($user, $body);
    }
}
