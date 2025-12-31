<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Payment
{
    public const MODE_BEFORE = 'BEFORE';
    public const MODE_AFTER = 'AFTER';

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_REQUIRES_ACTION = 'REQUIRES_ACTION';
    public const STATUS_PAID = 'PAID';
    public const STATUS_FAILED = 'FAILED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?JobRequest $jobRequest = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offer $offer = null;

    #[ORM\ManyToOne(inversedBy: 'paymentsAsPayer')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $payer = null;

    #[ORM\ManyToOne(inversedBy: 'paymentsAsPayee')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $payee = null;

    #[ORM\Column(length: 10)]
    private string $mode = self::MODE_BEFORE;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePaymentIntentId = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?string $amount = null;

    #[ORM\Column(length: 3)]
    private string $currency = 'EUR';

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $afterWorkCodeRequester = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $afterWorkCodeProposer = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $requesterValidatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $proposerValidatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobRequest(): ?JobRequest
    {
        return $this->jobRequest;
    }

    public function setJobRequest(?JobRequest $jobRequest): static
    {
        $this->jobRequest = $jobRequest;
        return $this;
    }

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(?Offer $offer): static
    {
        $this->offer = $offer;
        return $this;
    }

    public function getPayer(): ?User
    {
        return $this->payer;
    }

    public function setPayer(?User $payer): static
    {
        $this->payer = $payer;
        return $this;
    }

    public function getPayee(): ?User
    {
        return $this->payee;
    }

    public function setPayee(?User $payee): static
    {
        $this->payee = $payee;
        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): static
    {
        if (!in_array($mode, [self::MODE_BEFORE, self::MODE_AFTER])) {
            throw new \InvalidArgumentException("Invalid mode: $mode");
        }
        $this->mode = $mode;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_REQUIRES_ACTION, self::STATUS_PAID, self::STATUS_FAILED])) {
            throw new \InvalidArgumentException("Invalid status: $status");
        }
        $this->status = $status;
        return $this;
    }

    public function getStripePaymentIntentId(): ?string
    {
        return $this->stripePaymentIntentId;
    }

    public function setStripePaymentIntentId(?string $stripePaymentIntentId): static
    {
        $this->stripePaymentIntentId = $stripePaymentIntentId;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function getAfterWorkCodeRequester(): ?string
    {
        return $this->afterWorkCodeRequester;
    }

    public function setAfterWorkCodeRequester(?string $afterWorkCodeRequester): static
    {
        $this->afterWorkCodeRequester = $afterWorkCodeRequester;
        return $this;
    }

    public function getAfterWorkCodeProposer(): ?string
    {
        return $this->afterWorkCodeProposer;
    }

    public function setAfterWorkCodeProposer(?string $afterWorkCodeProposer): static
    {
        $this->afterWorkCodeProposer = $afterWorkCodeProposer;
        return $this;
    }

    public function getRequesterValidatedAt(): ?\DateTimeImmutable
    {
        return $this->requesterValidatedAt;
    }

    public function setRequesterValidatedAt(?\DateTimeImmutable $requesterValidatedAt): static
    {
        $this->requesterValidatedAt = $requesterValidatedAt;
        return $this;
    }

    public function getProposerValidatedAt(): ?\DateTimeImmutable
    {
        return $this->proposerValidatedAt;
    }

    public function setProposerValidatedAt(?\DateTimeImmutable $proposerValidatedAt): static
    {
        $this->proposerValidatedAt = $proposerValidatedAt;
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

    public function isBothCodesValidated(): bool
    {
        return $this->requesterValidatedAt !== null && $this->proposerValidatedAt !== null;
    }
}

