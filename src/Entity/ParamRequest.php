<?php

namespace App\Entity;

use App\Repository\ParamRequestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ParamRequest
 *
 * This entity stores information about API requests made to Clash of Clans.
 * It is used to control the number of requests and avoid overloading the API.
 * It keeps the clan tag, the request date, and the parameters used.
 */

#[ORM\Entity(repositoryClass: ParamRequestRepository::class)]
class ParamRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $clanId = null;

    #[ORM\Column(length: 255)]
    private ?string $date = null;

    #[ORM\Column(length: 255)]
    private ?string $parameters = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClanId(): ?string
    {
        return $this->clanId;
    }

    public function setClanId(string $clanId): static
    {
        $this->clanId = $clanId;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getParameters(): ?string
    {
        return $this->parameters;
    }

    public function setParameters(string $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }
}
