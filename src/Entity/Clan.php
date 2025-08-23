<?php

namespace App\Entity;

use App\Repository\ClansRepository;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class Clan
 *
 * This entity represents a Clash of Clans clan.
 * It stores the clan identifier (tag) and the clan name.
 *
 * Relations:
 * - One clan can have many attacks.
 * - One clan can have many forum posts.
 */

#[ORM\Entity(repositoryClass: ClansRepository::class)]
class Clan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $clan_id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClanId(): ?string
    {
        return $this->clan_id;
    }

    public function setClanId(string $clan_id): static
    {
        $this->clan_id = $clan_id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
