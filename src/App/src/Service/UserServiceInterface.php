<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\UserInterface;
use Doctrine\ORM\EntityRepository;

interface UserServiceInterface
{
    public const AUTH_AUTHENTICATION = 'authentication';
    public const AUTH_REGISTRATION   = 'registration';
    public const AUTH_LOGIN          = 'login';

    public const AUTH_REGISTRATION_TYPES = [
        self::AUTH_AUTHENTICATION,
        self::AUTH_REGISTRATION,
    ];

    public const AUTH_TYPES = [
        self::AUTH_AUTHENTICATION,
        self::AUTH_REGISTRATION,
        self::AUTH_LOGIN,
    ];

    public function activate(string $hash): void;

    public function loginWithHash(string $hash): string;

    public function confirmation(array $filteredData, string $hash): void;

    public function newsletterActivateSimple(UserInterface $user): void;

    public function prizeActivateSimple(UserInterface $user): void;

    public function prizeActivate(string $prizeHash): void;

    public function resetPassword(string $hash, string $password): void;

    public function changePersonalData(UserInterface $user, array $filteredParams): void;

    public function changeAboutData(UserInterface $user, array $filteredParams): void;

    public function forgotPassword(string $email): void;

    public function accountConfirmation(UserInterface $user): void;

    public function accountLoginWithMagicLink(UserInterface $user, ?string $pathname = null): void;

    public function accountLoginWithMagicLinkIsNewAccount(UserInterface $user, ?string $pathname = null): void;

    public function accountLoginWithMagicLinkAuthentication(UserInterface $user, ?string $pathname = null): void;

    public function accountLoginNoHasAccount(string $email): void;

    public function sendPrizeNotification(UserInterface $user): void;

    public function registration(array $filteredParams): UserInterface;

    public function getRepository(): EntityRepository;
}
