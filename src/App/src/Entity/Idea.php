<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\EntityMetaTrait;
use App\Traits\EntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

use function array_slice;
use function count;
use function explode;
use function implode;
use function min;
use function strip_tags;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IdeaRepository")
 * @ORM\Table(name="ideas", indexes={
 *     @ORM\Index(name="idea_search_idx", columns={"title"})
 * })
 */
class Idea implements IdeaInterface
{
    use EntityMetaTrait;
    use EntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="ideas")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", nullable=false)
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private Campaign $campaign;

    /**
     * @ORM\ManyToOne(targetEntity="CampaignTheme")
     * @ORM\JoinColumn(name="campaign_theme_id", referencedColumnName="id", nullable=false)
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private CampaignTheme $campaignTheme;

    /**
     * @ORM\ManyToOne(targetEntity="CampaignTopic")
     * @ORM\JoinColumn(name="campaign_topic_id", referencedColumnName="id", nullable=false)
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private CampaignTopic $campaignTopic;

    /**
     * @ORM\ManyToOne(targetEntity="CampaignLocation")
     * @ORM\JoinColumn(name="campaign_location_id", referencedColumnName="id", nullable=true)
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private ?CampaignLocation $campaignLocation;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="ideas")
     * @ORM\JoinColumn(name="submitter_id", referencedColumnName="id", nullable=false)
     *
     * @Groups({"detail", "full_detail"})
     */
    private User $submitter;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="ideas")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=true)
     *
     * @Groups({"detail", "full_detail"})
     */
    private ?Project $project;

    /**
     * @ORM\ManyToOne(targetEntity="WorkflowState")
     * @ORM\JoinColumn(name="workflow_state_id", referencedColumnName="id", nullable=false)
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private WorkflowState $workflowState;

    /**
     * @ORM\ManyToOne(targetEntity="WorkflowStateExtra")
     * @ORM\JoinColumn(name="workflow_state_extra_id", referencedColumnName="id", nullable=true)
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private ?WorkflowStateExtra $workflowStateExtra;

    /**
     * @ORM\ManyToMany(targetEntity="Media")
     * @ORM\JoinTable(name="ideas_medias",
     *      joinColumns={@ORM\JoinColumn(name="idea_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="media_id", referencedColumnName="id")}
     * )
     *
     * @Groups({"detail", "full_detail"})
     * @var Collection|Media[]
     */
    private Collection $medias;

    /**
     * @ORM\OneToMany(targetEntity="Link", mappedBy="idea")
     *
     * @Groups({"detail", "full_detail"})
     * @var Collection|Link[]
     */
    private Collection $links;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="idea")
     *
     * @var Collection|Comment[]
     *
     * @Groups({"detail", "full_detail"})
     */
    private Collection $comments;

    /**
     * @ORM\OneToMany(targetEntity="IdeaCampaignLocation", mappedBy="idea")
     *
     * @var Collection|IdeaCampaignLocation[]
     *
     * @Groups({"detail", "full_detail"})
     */
    private Collection $ideaCampaignLocations;

    /**
     * @ORM\Column(name="title", type="string")
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private string $title;

    /**
     * @ORM\Column(name="solution", type="text")
     *
     * @Groups({"detail", "full_detail"})
     */
    private string $solution;

    /**
     * @ORM\Column(name="description", type="text")
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private string $description;

    /**
     * @ORM\Column(name="location_description", type="text", nullable=false)
     *
     * @Groups({"list", "detail", "full_detail"})
     */
    private string $locationDescription = "";

    /**
     * @ORM\Column(name="participate", type="boolean", nullable=false)
     *
     * @Groups({"full_detail"})
     */
    private bool $participate;

    /**
     * @ORM\Column(name="participate_comment", type="text", nullable=false)
     *
     * @Groups({"full_detail"})
     */
    private string $participateComment = "";

    /**
     * @ORM\Column(name="cost", type="bigint", options={"unsigned"=true}, nullable=true)
     *
     * @Groups({"list", "detail", "full_detail"})
     * @var string|int|null
     */
    private $cost;

    /**
     * @ORM\Column(name="cost_condition", type="boolean", nullable=true)
     *
     * @Groups({"list", "detail", "full_detail"})
     * @var bool|null
     */
    private $costCondition;

    /**
     * @ORM\Column(name="latitude", type="float", nullable=true)
     *
     * @Groups({"full_detail"})
     */
    private ?float $latitude;

    /**
     * @ORM\Column(name="longitude", type="float", nullable=true)
     *
     * @Groups({"full_detail"})
     */
    private ?float $longitude;

    /**
     * @ORM\Column(name="answer", type="text", nullable=false)
     *
     * @Groups({"detail", "full_detail"})
     */
    private string $answer = "";

    public function __construct()
    {
        $this->links                 = new ArrayCollection();
        $this->comments              = new ArrayCollection();
        $this->ideaCampaignLocations = new ArrayCollection();
        $this->medias                = new ArrayCollection();
    }

    public function getSubmitter(): UserInterface
    {
        return $this->submitter;
    }

    public function setSubmitter(UserInterface $submitter): void
    {
        $this->submitter = $submitter;
    }

    public function getCampaign(): CampaignInterface
    {
        return $this->campaign;
    }

    public function setCampaign(CampaignInterface $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function getCampaignTheme(): CampaignThemeInterface
    {
        return $this->campaignTheme;
    }

    public function setCampaignTheme(CampaignThemeInterface $campaignTheme): void
    {
        $this->campaignTheme = $campaignTheme;
    }

    public function getCampaignTopic(): CampaignTopicInterface
    {
        return $this->campaignTopic;
    }

    public function setCampaignTopic(CampaignTopicInterface $campaignTopic): void
    {
        $this->campaignTopic = $campaignTopic;
    }

    public function getCampaignLocation(): ?CampaignLocationInterface
    {
        return $this->campaignLocation;
    }

    public function setCampaignLocation(?CampaignLocationInterface $campaignLocation = null): void
    {
        $this->campaignLocation = $campaignLocation;
    }

    public function getProject(): ?ProjectInterface
    {
        return $this->project;
    }

    public function setProject(?ProjectInterface $project = null): void
    {
        $this->project = $project;
    }

    public function getMedias(): array
    {
        $medias = [];
        foreach ($this->medias->getValues() as $media) {
            $medias[] = [
                'id'   => $media->getId(),
                'type' => $media->getType(),
            ];
        }

        return $medias;
    }

    public function getMediaCollection(): Collection
    {
        return $this->medias;
    }

    public function addMedia(MediaInterface $media): self
    {
        if (! $this->medias->contains($media)) {
            $this->medias[] = $media;
        }

        return $this;
    }

    public function removeMedia(MediaInterface $media): self
    {
        if ($this->medias->contains($media)) {
            $this->medias->removeElement($media);
        }

        return $this;
    }

    public function getLinks(): array
    {
        $links = [];
        foreach ($this->links->getValues() as $link) {
            $links[] = $link->getHref();
        }

        return $links;
    }

    public function addLink(Link $link): self
    {
        if (! $this->links->contains($link)) {
            $this->links[] = $link;
        }

        return $this;
    }

    public function removeLink(Link $link): self
    {
        if ($this->links->contains($link)) {
            $this->links->removeElement($link);
        }

        return $this;
    }

    public function getCommentCollection(): Collection
    {
        return $this->comments;
    }

    public function getComments(): array
    {
        $comments = [];
        foreach ($this->comments->getValues() as $comment) {
            $comments[] = $comment;
        }

        return $comments;
    }

    public function addComment(CommentInterface $comment): self
    {
        if (! $this->comments->contains($comment)) {
            $this->comments[] = $comment;
        }

        return $this;
    }

    public function getIdeaCampaignLocationsCollection(): Collection
    {
        return $this->ideaCampaignLocations;
    }

    public function getIdeaCampaignLocations(): array
    {
        $ideaCampaignLocations = [];
        foreach ($this->ideaCampaignLocations->getValues() as $ideaCampaignLocation) {
            $ideaCampaignLocations[] = $ideaCampaignLocation;
        }

        return $ideaCampaignLocations;
    }

    public function addIdeaCampaignLocation(IdeaCampaignLocationInterface $ideaCampaignLocation): self
    {
        if (! $this->ideaCampaignLocations->contains($ideaCampaignLocation)) {
            $this->ideaCampaignLocations[] = $ideaCampaignLocation;
        }

        return $this;
    }

    public function setWorkflowState(WorkflowState $workflowState): void
    {
        $this->workflowState = $workflowState;
    }

    public function getWorkflowState(): WorkflowState
    {
        return $this->workflowState;
    }

    public function setWorkflowStateExtra(?WorkflowStateExtra $workflowStateExtra = null): void
    {
        $this->workflowStateExtra = $workflowStateExtra;
    }

    public function getWorkflowStateExtra(): ?WorkflowStateExtra
    {
        return $this->workflowStateExtra;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setLocationDescription(string $locationDescription): void
    {
        $this->locationDescription = $locationDescription;
    }

    public function getLocationDescription(): string
    {
        return $this->locationDescription;
    }

    public function setSolution(string $solution): void
    {
        $this->solution = $solution;
    }

    public function getSolution(): string
    {
        return $this->solution;
    }

    public function setParticipate(bool $participate): void
    {
        $this->participate = $participate;
    }

    public function getParticipate(): bool
    {
        return $this->participate;
    }

    public function setParticipateComment(string $participateComment): void
    {
        $this->participateComment = $participateComment;
    }

    public function getParticipateComment(): string
    {
        return $this->participateComment;
    }

    /** @param int|string|null $cost **/
    public function setCost($cost = null): void
    {
        $this->cost = $cost;
    }

    public function getCost(): ?int
    {
        return $this->cost !== null ? (int) $this->cost : null;
    }

    /** @param bool|null $costCondition **/
    public function setCostCondition($costCondition = null): void
    {
        $this->costCondition = $costCondition;
    }

    public function getCostCondition(): ?bool
    {
        return $this->costCondition;
    }

    public function getShortDescription(): string
    {
        $description = $this->getDescription();

        $description = strip_tags($description);

        $descriptions = explode(" ", $description);
        $descriptions = array_slice($descriptions, 0, min(22, count($descriptions) - 1));

        $description  = implode(" ", $descriptions);
        $description .= ' ...';

        return $description;
    }

    public function setLatitude(?float $latitude = null): void
    {
        $this->latitude = $latitude;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLongitude(?float $longitude = null): void
    {
        $this->longitude = $longitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }
}
