<?php

declare(strict_types=1);

namespace App\Handler\Dashboard;

use App\Entity\Idea;
use App\Entity\OfflineVote;
use App\Entity\User;
use App\Entity\Vote;
use App\Entity\WorkflowStateInterface;
use App\Repository\IdeaRepository;
use App\Repository\OfflineVoteRepository;
use App\Repository\UserRepository;
use App\Repository\VoteRepository;
use App\Service\SettingServiceInterface;
use App\Service\PhaseServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class GetHandler implements RequestHandlerInterface
{
    private IdeaRepository $ideaRepository;
    private UserRepository $userRepository;
    private VoteRepository $voteRepository;
    private OfflineVoteRepository $offlineVoteRepository;

    public function __construct(
        private EntityManagerInterface $em,
        private SettingServiceInterface $settingService,
        private PhaseServiceInterface $phaseService
    ) {
        $this->em                    = $em;
        $this->settingService        = $settingService;
        $this->phaseService          = $phaseService;
        $this->ideaRepository        = $this->em->getRepository(Idea::class);
        $this->userRepository        = $this->em->getRepository(User::class);
        $this->voteRepository        = $this->em->getRepository(Vote::class);
        $this->offlineVoteRepository = $this->em->getRepository(OfflineVote::class);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $phase   = $this->phaseService->getCurrentPhase();
        $setting = $this->settingService->getRepository()->find(1);

        $countIdeas          = $this->ideaRepository->count([]);
        $countIdeasPublished = $this->countPublished();
        $countIdeasRejected  = $this->countRejected();

        $countUsers        = $this->userRepository->count([]);
        $countVotes        = $this->voteRepository->numberOfVotes($phase->getCampaign()->getId());
        $countOfflineVotes = $this->offlineVoteRepository->numberOfVotes($phase->getCampaign()->getId());

        return new JsonResponse([
            'settings' => $setting,
            'phase'    => [
                'title' => $phase->getTitle(),
            ],
            'campaign' => [
                'title' => $phase->getCampaign()->getShortTitle(),
            ],
            'infos'    => [
                'countIdeas'          => $countIdeas,
                'countIdeasPublished' => $countIdeasPublished,
                'countIdeasRejected'  => $countIdeasRejected,
                'countUsers'          => $countUsers,
                'countVotes'          => $countVotes,
                'countOfflineVotes'   => $countOfflineVotes,
            ],
        ]);
    }

    private function countPublished(): int
    {
        return $this->ideaRepository->count([
            'workflowState' => [
                WorkflowStateInterface::STATUS_PUBLISHED,
                WorkflowStateInterface::STATUS_PUBLISHED_WITH_MOD,
            ],
        ]);
    }

    private function countRejected(): int
    {
        return $this->ideaRepository->count([
            'workflowState' => [
                WorkflowStateInterface::STATUS_TRASH,
            ],
        ]);
    }
}
