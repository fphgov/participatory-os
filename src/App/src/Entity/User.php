<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\EntityActiveTrait;
use App\Traits\EntityMetaTrait;
use App\Traits\EntityTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
    use EntityActiveTrait;
    use EntityMetaTrait;
    use EntityTrait;

    /**
     * @ORM\OneToOne(targetEntity="UserPreference", mappedBy="user", cascade={"persist"})
     *
     * @Ignore()
     */
    private ?UserPreference $userPreference;

    /**
     * @ORM\OneToMany(targetEntity="Vote", mappedBy="user", cascade={"persist"})
     *
     * @Ignore()
     * @var Collection|Vote[]
     */
    private Collection $votes;

    /**
     * @ORM\OneToMany(targetEntity="Idea", mappedBy="submitter")
     *
     * @Ignore()
     * @var Collection|Idea[]
     */
    private Collection $ideas;

    /**
     * @ORM\Column(name="username", type="string")
     *
     * @Groups({"profile"})
     */
    private string $username;

    /**
     * @ORM\Column(name="firstname", type="string")
     *
     * @Groups({"list", "detail", "full_detail", "profile"})
     */
    private string $firstname;

    /**
     * @ORM\Column(name="lastname", type="string")
     *
     * @Groups({"list", "detail", "full_detail", "profile"})
     */
    private string $lastname;

    /**
     * @ORM\Column(name="email", type="string", length=100, unique=true)
     *
     * @Groups({"full_detail", "profile"})
     */
    private string $email;

    /**
     * @ORM\Column(name="password", type="text")
     *
     * @Ignore()
     */
    private string $password;

    /**
     * @ORM\Column(name="role", type="string")
     *
     * @Ignore()
     */
    private string $role = 'user';

    /** @ORM\Column(name="hash", type="string", unique=true, nullable=true) */
    private ?string $hash;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     *
     * @Ignore()
     */
    protected DateTime $createdAt;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
        $this->ideas = new ArrayCollection();
    }

    public function setUserPreference(?UserPreference $userPreference = null): void
    {
        $this->userPreference = $userPreference;
    }

    public function getUserPreference(): ?UserPreference
    {
        return $this->userPreference;
    }

    public function getVoteCollection(): Collection
    {
        return $this->votes;
    }

    public function getVotes(): array
    {
        $votes = [];
        foreach ($this->votes->getValues() as $vote) {
            $votes[] = $vote->getId();
        }

        return $votes;
    }

    public function addVote(VoteInterface $vote): self
    {
        if (! $this->votes->contains($vote)) {
            $this->votes[] = $vote;
        }

        return $this;
    }

    public function getIdeaCollection(): Collection
    {
        return $this->ideas;
    }

    public function getIdeas(): array
    {
        $ideas = [];
        foreach ($this->ideas->getValues() as $idea) {
            $ideas[] = $idea->getId();
        }

        return $ideas;
    }

    public function addIdea(IdeaInterface $idea): self
    {
        if (! $this->ideas->contains($idea)) {
            $this->ideas[] = $idea;
        }

        return $this;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setHash(?string $hash = null): void
    {
        $this->hash = $hash;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function generateToken(): string
    {
        $uuid4 = Uuid::uuid4();

        return $uuid4->toString();
    }
}
