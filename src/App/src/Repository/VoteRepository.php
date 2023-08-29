<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Campaign;
use App\Entity\CampaignTheme;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Throwable;

class VoteRepository extends EntityRepository
{
    /** @return mixed|int */
    public function numberOfVotes(int $id)
    {
        $qb = $this->createQueryBuilder('v');

        $qb
            ->select('COUNT(1)')
            ->where('v.project = :id')
            ->setParameter('id', $id);

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (Throwable $th) {
        }

        return 0;
    }

    public function checkExistsVoteInCampaign(User $user, Campaign $campaign): bool
    {
        $qb = $this->createQueryBuilder('v')
                ->select('COUNT(1)')
                ->innerJoin(Project::class, 'p', Join::WITH, 'p.id = v.project')
                ->innerJoin(Campaign::class, 'c', Join::WITH, 'c.id = p.campaign')
                ->where('v.user = :user')
                ->andWhere('c.id = :campaign')
                ->setParameter('user', $user)
                ->setParameter('campaign', $campaign);

        $result = $qb->getQuery()->getSingleScalarResult();

        try {
            return (int) $result > 0;
        } catch (Throwable $th) {
        }

        return false;
    }

    public function checkExistsVoteInCampaignAndProject(User $user, Campaign $campaign, Project $project): bool
    {
        $qb = $this->createQueryBuilder('v')
            ->select('COUNT(1)')
            ->innerJoin(Project::class, 'p', Join::WITH, 'p.id = v.project')
            ->innerJoin(Campaign::class, 'c', Join::WITH, 'c.id = p.campaign')
            ->where('v.user = :user')
            ->andWhere('c.id = :campaign')
            ->andWhere('p.id = :project')
            ->setParameter('user', $user)
            ->setParameter('campaign', $campaign)
            ->setParameter('project', $project);

        $result = $qb->getQuery()->getSingleScalarResult();

        try {
            return (int) $result > 0;
        } catch (Throwable $th) {
        }

        return false;
    }

    public function checkExistsVoteInCampaignInCategory(User $user, Campaign $campaign, Project $project): bool
    {
        $qb = $this->createQueryBuilder('v')
            ->select('COUNT(1)')
            ->innerJoin(Project::class, 'p', Join::WITH, 'p.id = v.project')
            ->innerJoin(Campaign::class, 'c', Join::WITH, 'c.id = p.campaign')
            ->innerJoin(CampaignTheme::class, 'ct', Join::WITH, 'ct.id = p.campaignTheme')
            ->where('v.user = :user')
            ->andWhere('c.id = :campaign')
            ->andWhere('ct.id = :campaignTheme')
            ->setParameter('user', $user)
            ->setParameter('campaign', $campaign)
            ->setParameter('campaignTheme', $project->getCampaignTheme());

        $result = $qb->getQuery()->getSingleScalarResult();

        try {
            return (int) $result > 0;
        } catch (Throwable $th) {
        }

        return false;
    }

    public function getVotedProjects(User $user, Campaign $campaign): array
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->innerJoin(Project::class, 'p', Join::WITH, 'p.id = v.project')
            ->innerJoin(User::class, 'u', Join::WITH, 'u.id = v.project')
            ->innerJoin(Campaign::class, 'c', Join::WITH, 'c.id = p.campaign')
            ->where('v.user = :user')
            ->andWhere('c.id = :campaign')
            ->setParameter('user', $user)
            ->setParameter('campaign', $campaign)
            ->orderBy('v.createdAt', 'DESC');

        $result = $qb->getQuery()->getResult();

        $projects = [];

        foreach ($result as $result) {
            $projects[] = $result->getProject();
        }

        return $projects;
    }
}
