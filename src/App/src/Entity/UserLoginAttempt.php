<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use App\Traits\EntityTrait;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserLoginAttemptRepository")
 * @ORM\Table(name="user_login_attempt")
 */
class UserLoginAttempt implements UserLoginAttemptInterface
{
    use EntityTrait;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Groups({"list", "option", "detail", "full_detail", "vote_list"})
     */
    protected int $id;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="userLoginAttempt")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, unique=false)
     */
    private User $user;

    /**
     * @ORM\Column(name="is_failed", type="boolean")
     */
    private bool $isFailed = false;

    /**
     * @ORM\Column(name="timestamp", type="datetime", nullable=false)
     *
     * @Ignore()
     */
    protected DateTime $timestamp;

    public function __construct()
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setIsFailed(bool $isFailed): void
    {
        $this->isFailed = $isFailed;
    }

    public function getIsFailed(): bool
    {
        return $this->isFailed;
    }

    public function setTimestamp(DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }
}
