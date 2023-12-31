<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\EntityActiveTrait;
use App\Traits\EntityMetaTrait;
use App\Traits\EntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore as ignore;

/**
 * @ORM\Entity
 * @ORM\Table(name="campaign_themes")
 */
class CampaignTheme implements CampaignThemeInterface
{
    use EntityActiveTrait;
    use EntityMetaTrait;
    use EntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="campaignThemes")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", nullable=false)
     *
     * @ignore
     * @var Campaign
     */
    private $campaign;

    /**
     * @ORM\Column(name="code", type="string")
     *
     * @Groups({"list", "detail", "full_detail", "vote_list"})
     */
    private string $code;

    /**
     * @ORM\Column(name="name", type="string")
     *
     * @Groups({"list", "detail", "full_detail", "vote_list"})
     */
    private string $name;

    /**
     * @ORM\Column(name="description", type="text")
     *
     * @Groups({"detail", "full_detail"})
     */
    private string $description;

    /**
     * @ORM\Column(name="rgb", type="string")
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private string $rgb;

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getRgb(): string
    {
        return $this->rgb;
    }

    public function setRgb(string $rgb): void
    {
        $this->rgb = $rgb;
    }
}
