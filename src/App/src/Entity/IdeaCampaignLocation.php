<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\EntityMetaTrait;
use App\Traits\EntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="idea_campaign_location")
 */
class IdeaCampaignLocation implements IdeaCampaignLocationInterface
{
    use EntityMetaTrait;
    use EntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="CampaignLocation")
     * @ORM\JoinColumn(name="campaign_location_id", referencedColumnName="id", nullable=false)
     *
     * @Groups({"detail", "full_detail"})
     */
    private CampaignLocation $campaignLocation;

    /**
     * @ORM\ManyToOne(targetEntity="Idea")
     * @ORM\JoinColumn(name="idea_id", referencedColumnName="id", nullable=false)
     *
     * @Groups({"detail", "full_detail"})
     */
    private Idea $idea;

    public function getCampaignLocation(): CampaignLocation
    {
        return $this->campaignLocation;
    }

    public function setCampaignLocation(CampaignLocation $campaignLocation): void
    {
        $this->campaignLocation = $campaignLocation;
    }

    public function getIdea(): Idea
    {
        return $this->idea;
    }

    public function setIdea(Idea $idea): void
    {
        $this->idea = $idea;
    }
}
