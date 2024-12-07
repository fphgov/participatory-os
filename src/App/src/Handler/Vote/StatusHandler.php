<?php

declare(strict_types=1);

namespace App\Handler\Vote;

use App\Entity\Campaign;
use App\Entity\Setting;
use App\Entity\VoteType;
use App\Entity\Vote;
use App\Service\VoteValidationServiceInterface;
use App\Exception\VoteTypeNoExistsInDatabaseException;
use App\Middleware\CampaignMiddleware;
use App\Middleware\UserMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class StatusHandler implements RequestHandlerInterface
{
    private EntityRepository $voteRepository;
    private EntityRepository $campaignRepository;

    public function __construct(
        private EntityManagerInterface $em,
    )
    {
        $this->voteRepository     = $this->em->getRepository(Vote::class);
        $this->campaignRepository = $this->em->getRepository(Campaign::class);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user     = $request->getAttribute(UserMiddleware::class);
        $campaign = $request->getAttribute(CampaignMiddleware::class);

        $projects = $this->voteRepository->getVotedProjects($user, $campaign);

        $voteCount = $this->getVoteCountPerProject();
        $maxCount  = $voteCount * 5;

        $countCampaignThemes = $this->getAllVoteableProjects($campaign, $voteCount);

        $normalizedProjects = [];
        foreach ($projects as $project) {
            $normalizedProjects[] = $project->normalizer(null, ['groups' => 'vote_list']);

            if (isset($countCampaignThemes[$project->getCampaignTheme()->getCode()])) {
                $countCampaignThemes[$project->getCampaignTheme()->getCode()]--;
            }
        }

        return new JsonResponse([
            'data' =>  [
                'voteables_count'                    => $maxCount - count($normalizedProjects),
                'voteables_count_by_campaign_themes' => $countCampaignThemes,
                'projects'                           => $normalizedProjects,
            ],
        ]);
    }

    private function getAllVoteableProjects(Campaign $campaign, int $voteCount)
    {
        $voteableCampaignThemes = $this->campaignRepository->getAllVoteableCampaignTheme($campaign);

        $countCampaignThemes = [];

        foreach ($voteableCampaignThemes as $campaignThemes) {
            $countCampaignThemes[$campaignThemes->getCode()] = $voteCount;
        }

        return $countCampaignThemes;
    }

    private function getVoteCountPerProject()
    {
        $type = $this->em->getRepository(Setting::class)->findOneBy([
            'key' => 'vote-type',
        ]);

        if (! $type) {
            throw new VoteTypeNoExistsInDatabaseException('Vote type no exists in database');
        }

        $voteType = $this->em->getReference(VoteType::class, $type->getValue());

        return ($voteType->getId() === 4 ? VoteValidationServiceInterface::VOTE_TYPE_4_COUNT : VoteValidationServiceInterface::VOTE_TYPE_DEFAULT_COUNT);
    }
}
