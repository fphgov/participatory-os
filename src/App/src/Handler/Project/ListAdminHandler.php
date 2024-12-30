<?php

declare(strict_types=1);

namespace App\Handler\Project;

use App\Entity\Campaign;
use App\Entity\OfflineVote;
use App\Entity\Project;
use Doctrine\ORM\EntityManager;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ListAdminHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManager $em
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $campaignRepository    = $this->em->getRepository(Campaign::class);
        $projectRepository     = $this->em->getRepository(Project::class);
        $offlineVoteRepository = $this->em->getRepository(OfflineVote::class);

        $campaigns = $campaignRepository->getForSelection();
        $projects = $projectRepository->getForSelection();
        $stats    = $offlineVoteRepository->getStatistics();

        return new JsonResponse([
            'campaigns' => $campaigns,
            'projects'  => $projects,
            'stats'     => $stats,
        ]);
    }
}
