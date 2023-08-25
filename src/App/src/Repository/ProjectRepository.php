<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CampaignInterface;
use App\Entity\WorkflowStateInterface;
use App\Entity\Campaign;
use App\Entity\CampaignTheme;
use App\Entity\Vote;
use App\Entity\WorkflowState;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use App\Model\VoteableProjectFilterModel;
use Doctrine\ORM\QueryBuilder;

final class ProjectRepository extends EntityRepository
{
    public function getForSelection(?int $campaignTheme = null): array
    {
        $filteredProjects = $this->findAll();

        $selectables = [];
        foreach ($filteredProjects as $project) {
            if (! isset($selectables[$project->getCampaignTheme()->getId()])) {
                $selectables[$project->getCampaignTheme()->getId()] = [
                    'id'       => $project->getCampaignTheme()->getId(),
                    'name'     => $project->getCampaignTheme()->getName(),
                    'code'     => $project->getCampaignTheme()->getCode(),
                    'campaign' => $project->getCampaign()->getId(),
                    'elems'    => [],
                ];
            }

            $selectables[$project->getCampaignTheme()->getId()]['elems'][] = [
                'id'   => $project->getId(),
                'name' => $project->getTitle(),
            ];
        }

        return $selectables;
    }

    public function getWorkflowStates(string $campaign = ''): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->where('p.workflowState NOT IN (:disableWorkflowStates)')
            ->groupBy('p.workflowState');

        if ($campaign !== '') {
            $qb->andWhere('p.campaign IN (:campaign)');
            $qb->setParameter('campaign', $campaign);
        }

        $qb->setParameter('disableWorkflowStates', [
            WorkflowStateInterface::STATUS_RECEIVED,
            WorkflowStateInterface::STATUS_USER_DELETED,
            WorkflowStateInterface::STATUS_TRASH,
        ]);

        $workflowStates = [];

        foreach ($qb->getQuery()->getResult() as $idea) {
            $workflowStates[] = $idea->getWorkflowState();
        }

        return $workflowStates;
    }

    public function getVoteables(
        CampaignInterface $campaign,
        VoteableProjectFilterModel $voteableProjectFilterModel
    ): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('NEW VoteableProjectListDTO(p.id, c.shortTitle, ct.name, p.title, p.description, p.location, w.code, w.title, COUNT(distinct v.id), GROUP_CONCAT(t.id), GROUP_CONCAT(t.name)) as project')
            ->join(CampaignTheme::class, 'ct', Join::WITH, 'ct.id = p.campaignTheme')
            ->join(Campaign::class, 'c', Join::WITH, 'c.id = ct.campaign')
            ->join(WorkflowState::class, 'w', Join::WITH, 'w.id = p.workflowState')
            ->leftJoin(Vote::class, 'v', Join::WITH, 'p.id = v.project')
            ->leftJoin('p.tags', 't')
            ->leftJoin('p.campaignLocations', 'cl')
            ->where('p.workflowState = :workflowState')
            ->andWhere('p.campaign = :campaign')
            ->groupBy('p.id')
            ->setParameters([
                'workflowState' => WorkflowStateInterface::STATUS_VOTING_LIST,
                'campaign'      => $campaign,
            ]);

        if ($voteableProjectFilterModel->getRand() !== null) {
            $qb->orderBy('RAND(:rand)');
            $qb->setParameter('rand', $voteableProjectFilterModel->getRand());
        }

        $query    = $voteableProjectFilterModel->getQuery();
        $location = $voteableProjectFilterModel->getLocation();

        if (intval($query) !== 0) {
            $qb->where('p.id = :id')->setParameter('id', $query);
        } elseif ($query) {
            $qb
                ->where('p.title LIKE :title')->setParameter('title', "%" . $query . "%")
                ->orWhere('p.description LIKE :description')->setParameter('description', "%" . $query . "%")
                ->orWhere('p.solution LIKE :solution')->setParameter('solution', "%" . $query . "%");
        }

        if ($voteableProjectFilterModel->getTag()) {
            $qb->andWhere('t.id = :tags');
            $qb->setParameter('tags', $voteableProjectFilterModel->getTag());
        }

        if ($voteableProjectFilterModel->getTheme() && $voteableProjectFilterModel->getTheme() !== 0) {
            $qb->andWhere('ct.code = :themes');
            $qb->setParameter('themes', strtoupper($voteableProjectFilterModel->getTheme()));
        }

        if ($location && intval($location) && $location !== 0) {
            $qb->andWhere('cl.id = :location');
            $qb->setParameter('location', $location);
        }

        if ($location && is_string($location) && $location !== 0) {
            $qb->andWhere('cl.code = :location');
            $qb->setParameter('location', $location);
        }

        $qb->setMaxResults(1);

        return $qb;
    }
}
