<?php

declare(strict_types=1);

namespace App\Entity;

use App\Interfaces\EntityInterface;

interface OfflineVoteInterface extends EntityInterface
{
    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): void;

    public function setProject(ProjectInterface $project): void;

    public function getProject(): ProjectInterface;

    public function getVoteType(): VoteTypeInterface;

    public function setVoteType(VoteTypeInterface $voteType): void;
}
