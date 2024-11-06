<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\EntityTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore as ignore;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BlacklistedTokensRepository")
 * @ORM\Table(name="blacklisted_tokens")
 */
class BlacklistedTokens implements BlacklistedTokenstInterface
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
     * @ORM\Column(name="token", type="string", unique=true, nullable=false)
     */
    private string $token;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     *
     * @Ignore()
     */
    protected DateTime $createdAt;

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

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
