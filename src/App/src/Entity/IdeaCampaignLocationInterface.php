<?php

declare(strict_types=1);

namespace App\Entity;

use App\Interfaces\EntityInterface;

interface IdeaCampaignLocationInterface extends EntityInterface
{
    public function getCampaignLocation(): CampaignLocation;

    public function setCampaignLocation(CampaignLocation $campaignLocation): void;

    public function getIdea(): Idea;

    public function setIdea(Idea $idea): void;
}
