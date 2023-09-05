<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Vote;
use App\Entity\Phase;
use App\Entity\Campaign;
use App\Entity\OfflineVote;
use App\Entity\Project;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityManagerInterface;

final class VoteExportModel
{
    public const HEADER = [
        'id',
        'user',
        'project_id',
        'project_title',
        'project_price',
        'type',
        'created',
    ];

    public function __construct(
        private EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    public function getCsvData(Phase $phase, array $queryParams): array
    {
        $campaignParam = $queryParams['campaign'] ?? '';

        $campaign = $phase->getCampaign();

        if ($campaignParam) {
            $campaignRepository = $this->em->getRepository(Campaign::class);

            $campaign = $campaignRepository->find($campaignParam);
        }

        $onlineResults  = $this->getOnlineVoteList($campaign, true);
        $offlineResults = $this->getOfflineVoteList($campaign, true);

        $exportData = [];

        $exportData[] = self::HEADER;

        foreach ($onlineResults as $onlineResult) {
            $data = $onlineResult;
            $data['online'] = true;

            $exportData[] = $data;
        }

        foreach ($offlineResults as $offlineResult) {
            $data = $offlineResult;
            $data['online'] = false;

            $exportData[] = $data;
        }

        return $exportData;
    }

    private function getOnlineVoteList(Campaign $campaign): array
    {
        $voteRepository = $this->em->getRepository(Vote::class);

        $qb = $voteRepository->createQueryBuilder('v')
            ->join(Project::class, 'p', Join::WITH, 'p.id = v.project')
            ->join(Campaign::class, 'c', Join::WITH, 'c.id = p.campaign')
            ->where('p.campaign = :campaign')
            ->orderBy('p.id', 'DESC')
            ->setParameters([
                'campaign' => $campaign,
            ]);

        $votes = $qb->getQuery()->getResult();

        return $this->normalizeVote($votes);
    }

    private function getOfflineVoteList(Campaign $campaign): array
    {
        $offlineVoteRepository = $this->em->getRepository(OfflineVote::class);

        $qb = $offlineVoteRepository->createQueryBuilder('v')
            ->join(Project::class, 'p', Join::WITH, 'p.id = v.project')
            ->join(Campaign::class, 'c', Join::WITH, 'c.id = p.campaign')
            ->where('p.campaign = :campaign')
            ->orderBy('p.id', 'DESC')
            ->setParameters([
                'campaign' => $campaign,
            ]);

        $votes = $qb->getQuery()->getResult();

        return $this->normalizeVote($votes);
    }

    private function normalizeVote(array $votes): array
    {
        $normalizedVotes = [];
        foreach ($votes as $vote) {
            $normVote = [
                'id'            => $vote->getId(),
                'user'          => $vote->getUser()->getId(),
                'project_id'    => $vote->getProject()->getId(),
                'project_title' => $vote->getProject()->getTitle(),
                'project_price' => $vote->getProject()->getCost(),
                'type'          => $vote->getVoteType()->getId(),
                'created'       => $vote->getCreatedAt()->format('Y-m-d H:i:s'),
            ];

            $normalizedVotes[] = $normVote;
        }

        return $normalizedVotes;
    }
}
