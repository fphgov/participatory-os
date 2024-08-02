<?php

declare(strict_types=1);

namespace App\Entity;

use JsonSerializable;

class ProjectStatisticsDTO implements JsonSerializable
{
    private int $id;
    private int $campaignThemeId;
    private string $campaignThemeName;
    private string $campaignThemeRgb;
    private string $title;
    private string $type;
    private int $votedCount;
    private int $plusVoted = 0;
    private bool $win;

    public function __construct(
        int $id,
        int $campaignThemeId,
        string $campaignThemeName,
        string $campaignThemeRgb,
        string $title,
        string $type,
        int $votedCount,
        bool $win
    ) {
        $this->id                = $id;
        $this->campaignThemeId   = $campaignThemeId;
        $this->campaignThemeName = $campaignThemeName;
        $this->campaignThemeRgb  = $campaignThemeRgb;
        $this->title             = $title;
        $this->type              = $type;
        $this->votedCount        = $votedCount;
        $this->win               = $win;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCampaignTheme(): array
    {
        return [
            'id'   => $this->campaignThemeId,
            'name' => $this->campaignThemeName,
            'rgb'  => $this->campaignThemeRgb,
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getWin(): bool
    {
        return $this->win;
    }

    public function getVoteCount(): int
    {
        return $this->plusVoted + $this->votedCount;
    }

    public function setPlusVoted(int $vote): void
    {
        $this->plusVoted = $vote;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'             => $this->getId(),
            'title'          => $this->getTitle(),
            'campaign_theme' => $this->getCampaignTheme(),
            'voted'          => $this->getVoteCount(),
            'type'           => $this->getType(),
            'win'            => $this->getWin(),
        ];
    }
}
