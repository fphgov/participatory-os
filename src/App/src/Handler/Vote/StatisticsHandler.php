<?php

declare(strict_types=1);

namespace App\Handler\Vote;

use App\Entity\Campaign;
use App\Entity\OfflineVote;
use App\Entity\Phase;
use App\Entity\Project;
use App\Entity\Vote;
use App\Service\PhaseServiceInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class StatisticsHandler implements RequestHandlerInterface
{
    /** @var EntityManager */
    protected $em;

    /** @var PhaseServiceInterface */
    protected $phaseService;

    public function __construct(
        EntityManager $em,
        PhaseServiceInterface $phaseService
    ) {
        $this->em           = $em;
        $this->phaseService = $phaseService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $phase = $this->phaseService->getCurrentPhase();

        $queryParams = $request->getQueryParams();
        $phaseParam  = $queryParams['phase'] ?? '';

        if ($phaseParam) {
            $phaseRepository = $this->em->getRepository(Phase::class);

            $phase = $phaseRepository->find($phaseParam);
        }

        $onlineResult  = $this->getOnlineVoteList($phase);
        $offlineResult = $this->getOfflineVoteList($phase);

        return new JsonResponse([
            'data' => [
                'online'  => $onlineResult,
                'offline' => $offlineResult
            ],
        ]);
    }

    private function getOnlineVoteList(Phase $phase): array
    {
        $voteRepository = $this->em->getRepository(Vote::class);

        $qb = $voteRepository->createQueryBuilder('v')
            ->join(Project::class, 'p', Join::WITH, 'p.id = v.project')
            ->join(Campaign::class, 'c', Join::WITH, 'c.id = p.campaign')
            ->where('p.campaign = :campaign')
            ->orderBy('p.id', 'DESC')
            ->setParameters([
                'campaign' => $phase->getCampaign(),
            ]);

        $votes = $qb->getQuery()->getResult();

        return $this->normalizeVote($votes);
    }

    private function getOfflineVoteList(Phase $phase): array
    {
        $offlineVoteRepository = $this->em->getRepository(OfflineVote::class);

        $qb = $offlineVoteRepository->createQueryBuilder('v')
            ->join(Project::class, 'p', Join::WITH, 'p.id = v.project')
            ->join(Campaign::class, 'c', Join::WITH, 'c.id = p.campaign')
            ->where('p.campaign = :campaign')
            ->orderBy('p.id', 'DESC')
            ->setParameters([
                'campaign' => $phase->getCampaign(),
            ]);

        $votes = $qb->getQuery()->getResult();

        return $this->normalizeVote($votes);
    }

    private function normalizeVote(array $votes): array
    {
        $normalizedVotes = [];
        foreach ($votes as $vote) {
            $normalizedVotes[] = [
                'id'      => $vote->getId(),
                'user'    => $vote->getUser()->getId(),
                'project' => $vote->getProject()->getId(),
                'type'    => $vote->getVoteType()->getId(),
                'created' => $vote->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return $normalizedVotes;
    }
}
