<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PhaseInterface;
use App\Entity\ProjectInterface;
use App\Entity\UserInterface;
use App\Entity\VoteTypeInterface;

interface VoteValidationServiceInterface
{
    public function checkExistsVote(
        UserInterface $user,
        PhaseInterface $phase,
        ?ProjectInterface $projectId = null
    ): void;

    public function validation(
        UserInterface $user,
        PhaseInterface $phase,
        VoteTypeInterface $voteType,
        array $projects
    ): void;
}
