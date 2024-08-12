<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CampaignInterface;
use App\Entity\PhaseInterface;
use App\Entity\ProjectInterface;
use App\Entity\UserInterface;
use App\Entity\VoteTypeInterface;

interface VoteValidationServiceInterface
{
    const VOTE_TYPE_DEFAULT_COUNT = 1;
    const VOTE_TYPE_4_COUNT = 3;

    public function checkExistsVote(
        UserInterface $user,
        PhaseInterface $phase,
        VoteTypeInterface $voteType,
        ?ProjectInterface $projectId = null
    ): void;

    public function validation(
        UserInterface $user,
        PhaseInterface $phase,
        VoteTypeInterface $voteType,
        array $projects
    ): void;

    public function getAvailableVoteCount(
        UserInterface $user,
        CampaignInterface $campaign,
        VoteTypeInterface $voteType,
        ProjectInterface $project
    ): int;
}
