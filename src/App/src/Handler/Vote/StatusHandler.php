<?php

declare(strict_types=1);

namespace App\Handler\Vote;

use App\Middleware\CampaignMiddleware;
use App\Middleware\UserMiddleware;
use App\Entity\Vote;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class StatusHandler implements RequestHandlerInterface
{
    private $voteRepository;

    public function __construct(
        private EntityManagerInterface $em,
    )
    {
        $this->em             = $em;
        $this->voteRepository = $this->em->getRepository(Vote::class);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user     = $request->getAttribute(UserMiddleware::class);
        $campaign = $request->getAttribute(CampaignMiddleware::class);

        $projects = $this->voteRepository->getVotedProjects($user, $campaign);

        $normalizedProjects = [];
        foreach ($projects as $project) {
            $normalizedProjects[] = $project->normalizer(null, ['groups' => 'vote_list']);
        }

        return new JsonResponse([
            'data' =>  [
                'voteables_count' => 5 - count($normalizedProjects),
                'projects'        => $normalizedProjects,
            ],
        ]);
    }
}
