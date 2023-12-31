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
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class GetHandler implements RequestHandlerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var SettingServiceInterface **/
    private $settingService;

    /** @var IdeaRepository **/
    private $ideaRepository;

    /** @var UserRepository **/
    private $userRepository;

    /** @var VoteRepository **/
    private $voteRepository;

    /** @var OfflineVoteRepository **/
    private $offlineVoteRepository;

    public function __construct(
        EntityManagerInterface $em,
        SettingServiceInterface $settingService
    ) {
        $this->em                    = $em;
        $this->settingService        = $settingService;
        $this->ideaRepository        = $this->em->getRepository(Idea::class);
        $this->userRepository        = $this->em->getRepository(User::class);
        $this->voteRepository        = $this->em->getRepository(Vote::class);
        $this->offlineVoteRepository = $this->em->getRepository(OfflineVote::class);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $setting = $this->settingService->getRepository()->find(1);

        $countIdeas          = $this->ideaRepository->count([]);
        $countIdeasPublished = $this->countPublished();
        $countIdeasRejected  = $this->countRejected();

        $countUsers        = $this->userRepository->count([]);
        $countVotes        = $this->voteRepository->count([]);
        $countOfflineVotes = $this->offlineVoteRepository->count([]);

        return new JsonResponse([
            'settings' => $setting,
            'infos'    => [
                'countIdeas'          => $countIdeas,
                'countIdeasPublished' => $countIdeasPublished,
                'countIdeasRejected'  => $countIdeasRejected,
                'countUsers'          => $countUsers,
                'countVotes'          => $countVotes / 3,
                'countOfflineVotes'   => $countOfflineVotes / 3,
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
