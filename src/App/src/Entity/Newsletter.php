<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\EntityMetaTrait;
use App\Traits\EntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NewsletterRepository")
 * @ORM\Table(name="newsletters")
 */
class Newsletter implements NewsletterInterface
{
    use EntityMetaTrait;
    use EntityTrait;

    /**
     * @ORM\Column(name="email", type="string", length=100, unique=true)
     *
     * @Groups({"full_detail", "profile"})
     */
    private string $email;

    /**
     * @ORM\Column(name="sync", type="boolean", nullable=false)
     *
     * @Groups({"full_detail"})
     */
    private bool $sync = false;

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setSync(bool $sync): void
    {
        $this->sync = $sync;
    }

    public function getSync(): bool
    {
        return $this->sync;
    }
}
