<?php

declare(strict_types=1);

namespace App\Entity;

interface UserLoginAttemptInterface
{
    public function getId(): int;

    public function setId(int $id): void;

    public function setUser(User $user): void;

    public function getUser(): User;

    public function setIsFailed(bool $isFailed): void;

    public function getIsFailed(): bool;
}
