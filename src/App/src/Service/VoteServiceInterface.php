<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\UserInterface;
use App\Entity\VoteTypeInterface;
use App\Model\VoteableProjectFilterModel;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

interface VoteServiceInterface
{
    public function addOfflineVote(
        UserInterface $user,
        int $projectId,
        int $type,
        int $voteCount
    ): void;

    public function voting(
        UserInterface $user,
        VoteTypeInterface $voteType,
        array $projects
    ): array;

    public function getRepository(): EntityRepository;

    public function getVoteablesProjects(
        VoteableProjectFilterModel $voteableProjectFilter,
        ?UserInterface $user = null
    ): QueryBuilder;
}
