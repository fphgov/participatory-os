<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\UserInterface;
use Doctrine\ORM\EntityRepository;

interface UserServiceInterface
{
    public function activate(string $hash): void;

    public function loginWithHash(string $hash): string;

    public function confirmation(array $filteredData, string $hash): void;

    public function newsletterActivateSimple(UserInterface $user): void;

    public function prizeActivateSimple(UserInterface $user): void;

    public function prizeActivate(string $prizeHash): void;

    public function resetPassword(string $hash, string $password): void;

    public function forgotPassword(string $email): void;

    public function accountConfirmation(UserInterface $user): void;

    public function accountLoginWithMagicLink(UserInterface $user, ?string $pathname = null): void;

    public function accountLoginNoHasAccount(string $email): void;

    public function sendPrizeNotification(UserInterface $user): void;

    public function registration(array $filteredParams): UserInterface;

    public function getRepository(): EntityRepository;
}
