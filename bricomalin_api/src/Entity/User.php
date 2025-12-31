<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $passwordHash = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?ProfessionalProfile $professionalProfile = null;

    #[ORM\OneToMany(mappedBy: 'requester', targetEntity: JobRequest::class)]
    private Collection $jobRequests;

    #[ORM\OneToMany(mappedBy: 'proposer', targetEntity: Offer::class)]
    private Collection $offers;

    #[ORM\OneToMany(mappedBy: 'payer', targetEntity: Payment::class)]
    private Collection $paymentsAsPayer;

    #[ORM\OneToMany(mappedBy: 'payee', targetEntity: Payment::class)]
    private Collection $paymentsAsPayee;

    public function __construct()
    {
        $this->jobRequests = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->paymentsAsPayer = new ArrayCollection();
        $this->paymentsAsPayee = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getProfessionalProfile(): ?ProfessionalProfile
    {
        return $this->professionalProfile;
    }

    public function setProfessionalProfile(?ProfessionalProfile $professionalProfile): static
    {
        if ($professionalProfile === null && $this->professionalProfile !== null) {
            $this->professionalProfile->setUser(null);
        }

        if ($professionalProfile !== null && $professionalProfile->getUser() !== $this) {
            $professionalProfile->setUser($this);
        }

        $this->professionalProfile = $professionalProfile;
        return $this;
    }

    public function isPro(): bool
    {
        return $this->professionalProfile !== null 
            && $this->professionalProfile->getStatus() === ProfessionalProfile::STATUS_VERIFIED;
    }

    /**
     * @return Collection<int, JobRequest>
     */
    public function getJobRequests(): Collection
    {
        return $this->jobRequests;
    }

    /**
     * @return Collection<int, Offer>
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPaymentsAsPayer(): Collection
    {
        return $this->paymentsAsPayer;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPaymentsAsPayee(): Collection
    {
        return $this->paymentsAsPayee;
    }
}

