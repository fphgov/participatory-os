<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\EntityActiveTrait;
use App\Traits\EntityMetaTrait;
use App\Traits\EntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CampaignRepository")
 * @ORM\Table(name="campaigns")
 */
class Campaign implements CampaignInterface
{
    use EntityActiveTrait;
    use EntityMetaTrait;
    use EntityTrait;

    /**
     * @ORM\OneToMany(targetEntity="Idea", mappedBy="campaign")
     *
     * @var Collection|Idea[]
     */
    private $ideas;

    /**
     * @ORM\OneToMany(targetEntity="CampaignTheme", mappedBy="campaign")
     *
     * @var Collection|CampaignTheme[]
     *
     * @Groups({"detail", "full_detail"})
     */
    private Collection $campaignThemes;

    /**
     * @ORM\OneToMany(targetEntity="CampaignTopic", mappedBy="campaign")
     *
     * @var Collection|CampaignTheme[]
     *
     * @Groups({"detail", "full_detail"})
     */
    private Collection $campaignTopics;

    /**
     * @ORM\OneToMany(targetEntity="CampaignLocation", mappedBy="campaign")
     *
     * @var Collection|CampaignLocation[]
     *
     * @Groups({"detail", "full_detail"})
     */
    private Collection $campaignLocations;

    /**
     * @ORM\Column(name="title", type="string")
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private string $title;

    /**
     * @ORM\Column(name="short_title", type="string")
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private string $shortTitle;

    /**
     * @ORM\Column(name="description", type="text")
     *
     * @Groups({"detail", "full_detail"})
     */
    private string $description;

    public function __construct()
    {
        $this->ideas             = new ArrayCollection();
        $this->campaignThemes    = new ArrayCollection();
        $this->campaignTopics    = new ArrayCollection();
        $this->campaignLocations = new ArrayCollection();
    }

    public function getIdeas(): array
    {
        $ideas = [];
        foreach ($this->ideas->getValues() as $idea) {
            $ideas[] = $idea->getId();
        }

        return $ideas;
    }

    public function getIdeaCollection(): Collection
    {
        return $this->ideas;
    }

    public function getCampaignThemes(): array
    {
        $campaignThemes = [];
        foreach ($this->campaignThemes->getValues() as $campaignTheme) {
            $campaignThemes[] = $campaignTheme->getId();
        }

        return $campaignThemes;
    }

    public function getCampaignThemesOptions(): array
    {
        $campaignThemes = [];
        foreach ($this->campaignThemes->getValues() as $campaignTheme) {
            $campaignThemes[] = [
                'id'   => $campaignTheme->getId(),
                'name' => $campaignTheme->getName()
            ];
        }

        return $campaignThemes;
    }

    public function getCampaignThemeCollection(): Collection
    {
        return $this->campaignThemes;
    }

    public function getCampaignTopics(): array
    {
        $campaignTopics = [];
        foreach ($this->campaignTopics->getValues() as $campaignTopic) {
            $campaignTopics[] = $campaignTopic->getId();
        }

        return $campaignTopics;
    }

    public function getCampaignTopicsOptions(): array
    {
        $campaignTopics = [];
        foreach ($this->campaignTopics->getValues() as $campaignTopic) {
            $campaignTopics[] = [
                'id'   => $campaignTopic->getId(),
                'name' => $campaignTopic->getName()
            ];
        }

        return $campaignTopics;
    }

    public function getCampaignTopicsCollection(): Collection
    {
        return $this->campaignTopics;
    }

    public function getCampaignLocations(): array
    {
        $campaignLocations = [];
        foreach ($this->campaignLocations->getValues() as $campaignLocation) {
            $campaignLocations[] = $campaignLocation->getId();
        }

        return $campaignLocations;
    }

    public function getCampaignLocationsOptions(): array
    {
        $campaignLocations = [];
        foreach ($this->campaignLocations->getValues() as $campaignLocation) {
            $campaignLocations[] = [
                'id'   => $campaignLocation->getId(),
                'code' => $campaignLocation->getCode(),
                'name' => $campaignLocation->getName()
            ];
        }

        return $campaignLocations;
    }

    public function getCampaignLocationCollection(): Collection
    {
        return $this->campaignLocations;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setShortTitle(string $shortTitle): void
    {
        $this->shortTitle = $shortTitle;
    }

    public function getShortTitle(): string
    {
        return $this->shortTitle;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
