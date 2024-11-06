<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;

interface BlacklistedTokenstInterface
{
    public function getId(): int;

    public function setId(int $id): void;

    public function getToken(): string;

    public function setToken(string $token): void;

    public function setCreatedAt(DateTime $createdAt): void;

    public function getCreatedAt(): DateTime;
}
