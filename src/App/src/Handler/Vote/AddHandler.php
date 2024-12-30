<?php

declare(strict_types=1);

namespace App\Handler\Vote;

use App\Entity\OfflineVote;
use App\Middleware\UserMiddleware;
use App\Service\VoteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function intval;

final class AddHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private InputFilterInterface $inputFilter,
        private VoteServiceInterface $voteService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user                  = $request->getAttribute(UserMiddleware::class);
        $offlineVoteRepository = $this->em->getRepository(OfflineVote::class);

        $this->inputFilter->setData($request->getParsedBody());

        if (! $this->inputFilter->isValid()) {
            return new JsonResponse([
                'errors' => $this->inputFilter->getMessages(),
            ], 422);
        }

        $values = $this->inputFilter->getValues();

        try {
            $this->voteService->addOfflineVote($user, intval($values['project']), 2, intval($values['voteCount']));
        } catch (Exception $e) {
            return new JsonResponse([
                'errors' => $e->getMessage(),
            ], 500);
        }

        $stats = $offlineVoteRepository->getStatistics();

        return new JsonResponse([
            'success' => true,
            'stats'   => $stats,
        ]);
    }
}
