<?php

declare(strict_types=1);

namespace App\Entity;

use App\Interfaces\EntityInterface;

interface UserPreferenceInterface extends EntityInterface
{
    public const DISABLE_SHOW_DEFAULT = [
        'createdAt',
        'updatedAt',
    ];

    public const DISABLE_DEFAULT_SET = [];

    public function setUser(User $user): void;

    public function getUser(): User;

    public function setBirthyear(?int $birthday = null): void;

    public function getBirthyear(): ?int;

    public function setLiveInCity(bool $liveInCity): void;

    public function getLiveInCity(): bool;

    public function setPostalCode(?string $postalCode = null): void;

    public function getPostalCode(): ?string;

    public function setPrivacy(bool $privacy): void;

    public function getPrivacy(): bool;

    public function setHearAbout(string $hearAbout): void;

    public function getHearAbout(): string;

    public function setPhone(?string $phone = null): void;

    public function getPhone(): ?string;

    public function setPrize(bool $prize): void;

    public function getPrize(): bool;

    public function setPrizeHash(?string $prizeHash = null): void;

    public function getPrizeHash(): ?string;

    public function setCampaignEmail(bool $campaignEmail): void;

    public function getCampaignEmail(): bool;

    public function setReminderEmail(bool $reminderEmail): void;

    public function getReminderEmail(): bool;
}
