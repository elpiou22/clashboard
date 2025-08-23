<?php
// src/Entity/PasswordResetRequest.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

/**
 * Class PasswordResetRequest
 *
 * This entity manages the reset of a user password.
 * It stores a unique selector, a hashed token, request date, expiry date,
 * and whether the request has been used.
 *
 * Relations:
 * - Each request is linked to exactly one user.
 */

#[ORM\Entity]
class PasswordResetRequest
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type:"integer")]
  private ?int $id = null;

  #[ORM\ManyToOne(targetEntity: User::class)]
  #[ORM\JoinColumn(nullable:false, onDelete:"CASCADE")]
  private ?User $user = null;

  #[ORM\Column(length:100)]       // identifiant public dans l'URL
  private string $selector;

  #[ORM\Column(length:100)]       // hash du token secret (jamais stocker le token en clair)
  private string $hashedToken;

  #[ORM\Column(type:"datetime_immutable")]
  private \DateTimeImmutable $requestedAt;

  #[ORM\Column(type:"datetime_immutable")]
  private \DateTimeImmutable $expiresAt;

  #[ORM\Column(type:"datetime_immutable", nullable:true)]
  private ?\DateTimeImmutable $usedAt = null;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function getUser(): ?\App\Entity\User
  {
    return $this->user;
  }

  public function setUser(?\App\Entity\User $user): void
  {
    $this->user = $user;
  }

  public function getSelector(): string
  {
    return $this->selector;
  }

  public function setSelector(string $selector): void
  {
    $this->selector = $selector;
  }

  public function getHashedToken(): string
  {
    return $this->hashedToken;
  }

  public function setHashedToken(string $hashedToken): void
  {
    $this->hashedToken = $hashedToken;
  }

  public function getRequestedAt(): \DateTimeImmutable
  {
    return $this->requestedAt;
  }

  public function setRequestedAt(\DateTimeImmutable $requestedAt): void
  {
    $this->requestedAt = $requestedAt;
  }

  public function getExpiresAt(): \DateTimeImmutable
  {
    return $this->expiresAt;
  }

  public function setExpiresAt(\DateTimeImmutable $expiresAt): void
  {
    $this->expiresAt = $expiresAt;
  }

  public function getUsedAt(): ?\DateTimeImmutable
  {
    return $this->usedAt;
  }

  public function setUsedAt(?\DateTimeImmutable $usedAt): void
  {
    $this->usedAt = $usedAt;
  }



}
