<?php

namespace App\Entity;

use App\Repository\AttackRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttackRepository::class)]
class Attack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    #[ORM\Column]
    private ?int $mapPosition = null;

  #[ORM\Column(length: 255)]
  private ?string $date = null;

  #[ORM\Column(length: 255)]
  private ?string $clanID = null;

  #[ORM\Column]
  private ?int $day = null;

  #[ORM\Column(length: 255)]
  private ?string $result = null;

  #[ORM\Column]
  private ?int $nbPosts = 0;

  // 11/08/2025: ajout champs pour le forum
  #[ORM\Column(length: 255)]
  private ?string $tag = null;

  #[ORM\Column]
  private ?int $attackerTH = null;

  #[ORM\Column]
  private ?int $defenderTH = null;

  #[ORM\Column]
  private ?int $percentage = null;

  #[ORM\Column]
  private ?int $attackStars = null;

  public function getId(): ?int
  {
      return $this->id;
  }

  public function getPseudo(): ?string
  {
      return $this->pseudo;
  }

  public function setPseudo(string $pseudo): static
  {
      $this->pseudo = $pseudo;

      return $this;
  }

  public function getMapPosition(): ?int
  {
      return $this->mapPosition;
  }

  public function setMapPosition(int $mapPosition): static
  {
      $this->mapPosition = $mapPosition;

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

  public function getDay(): ?int
  {
      return $this->day;
  }

  public function setDay(int $day): static
  {
      $this->day = $day;

      return $this;
  }

  public function getResult(): ?string
  {
      return $this->result;
  }

  public function setResult(string $result): static
  {
      $this->result = $result;

      return $this;
  }

  public function getClanID(): ?string
  {
    return $this->clanID;
  }

  public function setClanID(?string $clanID): void
  {
    $this->clanID = $clanID;
  }

  public function getNbPosts(): ?int
  {
    return $this->nbPosts;
  }

  public function setNbPosts(?int $nbPosts): void
  {
    $this->nbPosts = $nbPosts;
  }


  // 11/08/2025 - ajout champs pour forum
  public function getTag(): ?string
  {
    return $this->tag;
  }

  public function setTag(?string $tag): void
  {
    $this->tag = $tag;
  }

  public function getAttackerTH(): ?int
  {
    return $this->attackerTH;
  }

  public function setAttackerTH(?int $attackerTH): void
  {
    $this->attackerTH = $attackerTH;
  }

  public function getDefenderTH(): ?int
  {
    return $this->defenderTH;
  }

  public function setDefenderTH(?int $defenderTH): void
  {
    $this->defenderTH = $defenderTH;
  }

  public function getPercentage(): ?int
  {
    return $this->percentage;
  }

  public function setPercentage(?int $percentage): void
  {
    $this->percentage = $percentage;
  }

  public function getAttackStars(): ?int
  {
    return $this->attackStars;
  }

  public function setAttackStars(?int $attackStars): void
  {
    $this->attackStars = $attackStars;
  }







}
