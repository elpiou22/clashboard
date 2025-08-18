<?php

namespace App\Entity;

use App\Repository\TweetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Clan;

#[ORM\Entity(repositoryClass: TweetRepository::class)]
class Post
{


  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $text = null;

  #[ORM\Column]
  private ?int $author = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  private ?\DateTimeInterface $date = null;

  #[ORM\Column]
  private ?int $upvote = null;

  #[ORM\Column]
  private ?int $downvote = null;

  #[ORM\Column]
  private int $vote_weight = 0;

  #[ORM\Column]
  private ?int $reply_of = null;

  #[ORM\Column]
  private ?int $nb_replies = 0;

  #[ORM\ManyToOne(targetEntity: Clan::class)]
  #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
  private ?Clan $clan = null;

  #[ORM\Column(length: 255)]
  private ?string $cwlId = null;

  #[ORM\Column]
  private ?int $playerMapPosition = null;

  #[ORM\Column]
  private ?int $day = null;

  public function getId(): ?int
  {
      return $this->id;
  }

  public function getText(): ?string
  {
      return $this->text;
  }

  public function setText(string $text): static
  {
      $this->text = $text;

      return $this;
  }

  public function getAuthor(): ?int
  {
      return $this->author;
  }

  public function setAuthor(int $author): static
  {
      $this->author = $author;

      return $this;
  }

  public function getDate(): ?\DateTimeInterface
  {
      return $this->date;
  }

  public function setDate(\DateTimeInterface $date): static
  {
      $this->date = $date;

      return $this;
  }

  public function getUpvote(): ?int
  {
      return $this->upvote;
  }

  public function setUpvote(int $upvote): static
  {
      $this->upvote = $upvote;

      return $this;
  }

  public function getDownvote(): ?int
  {
      return $this->downvote;
  }

  public function setDownvote(int $downvote): static
  {
      $this->downvote = $downvote;

      return $this;
  }

  public function getClan(): ?Clan
  {
    return $this->clan;
  }

  public function setClan(?Clan $clan): self
  {
    $this->clan = $clan;
    return $this;
  }

  public function getCwlId(): ?string
  {
    return $this->cwlId;
  }

  public function setCwlId(?string $cwlId): void
  {
    $this->cwlId = $cwlId;
  }

  public function getPlayerMapPosition(): ?int
  {
    return $this->playerMapPosition;
  }

  public function setPlayerMapPosition(?int $playerMapPosition): void
  {
    $this->playerMapPosition = $playerMapPosition;
  }

  public function getDay(): ?int
  {
    return $this->day;
  }

  public function setDay(?int $day): void
  {
    $this->day = $day;
  }

  public function getReplyOf(): ?int
  {
    return $this->reply_of;
  }

  public function setReplyOf(?int $reply_of): void
  {
    $this->reply_of = $reply_of;
  }

  public function getNbReplies(): ?int
  {
    return $this->nb_replies;
  }

  public function setNbReplies(?int $nb_replies): void
  {
    $this->nb_replies = $nb_replies;
  }

  public function addNbReplies(): void
  {
    $this->nb_replies++;
  }

  public function getVoteWeight(): ?int
  {
    return $this->vote_weight;
  }

  public function setVoteWeight(int $vote_weight): void
  {
    $this->vote_weight = $vote_weight;
  }

  public function upvote(): self
  {
    $this->vote_weight++;
    $this->setUpvote($this->getUpvote()+1);
    return $this;
  }

  public function downvote(): self
  {
    $this->vote_weight--;
    $this->setDownvote($this->getDownvote() +1);
    return $this;
  }








  // //////////////////////////:

  /**
   * Récupère le pseudo d'un auteur en fonction de son ID.
   *
   * @param EntityManagerInterface $entityManager L'EntityManager pour interagir avec la base de données.
   * @return string|null Le pseudo de l'auteur ou null si aucun auteur n'est trouvé.
   */
  function getAuthorUsername(EntityManagerInterface $entityManager): ?string
  {

    $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $this->author]);

    return $user ? $user->getPseudo() : null;
  }



}
