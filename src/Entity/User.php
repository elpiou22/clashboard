<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[Vich\UploadableField(mapping: 'profile_pictures', fileNameProperty: 'profilePicture')]
    private ?File $profilePictureFile = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getProfilePictureFile(): ?File
    {
        return $this->profilePictureFile;
    }

    /**
     * @param File|null $profilePictureFile
     */
    public function setProfilePictureFile(?File $profilePictureFile): void
    {
        $this->profilePictureFile = $profilePictureFile;
        if ($profilePictureFile !== null) {
          $this->updatedAt = new \DateTimeImmutable();
        }
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
      $roles = $this->roles;
      // guarantee every user at least has ROLE_USER
      $roles[] = 'ROLE_USER';

      //15/08/2023 - Ajout PhpStan:
      return array_values(array_unique($roles));
      //return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }



  public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
  public function setUpdatedAt(?\DateTimeImmutable $d): self { $this->updatedAt = $d; return $this; }

  public function __serialize(): array
  {
    return [
        'id' => $this->id,
        'email' => $this->email,
        'password' => $this->password,
        'roles' => $this->roles,
        'pseudo' => $this->pseudo,
      // On peut stocker la date en string (ISO) pour éviter les pb de sérialisation
        'birthdate' => $this->birthdate?->format('c'),
        'profilePicture' => $this->profilePicture, // le NOM de fichier (string), pas l'objet File
    ];
  }

  public function __unserialize(array $data): void
  {
    $this->id = $data['id'];
    $this->email = $data['email'];
    $this->password = $data['password'];
    $this->roles = $data['roles'];
    $this->pseudo = $data['pseudo'];
    $this->birthdate = isset($data['birthdate']) ? new \DateTimeImmutable($data['birthdate']) : null;
    $this->profilePicture = $data['profilePicture'] ?? null;
  }

}
