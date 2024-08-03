<?php

declare(strict_types=1);

namespace App\Entity;

use App\Interfaces\EntityInterface;

interface NewsletterInterface extends EntityInterface
{
    public const DISABLE_SHOW_DEFAULT = [
        'createdAt',
        'updatedAt',
    ];

    public const DISABLE_DEFAULT_SET = [];

    public function setEmail(string $email): void;

    public function getEmail(): string;

    public function setSync(bool $sync): void;

    public function getSync(): bool;
}
