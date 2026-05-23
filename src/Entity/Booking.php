<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    public const STATUS_PENDING = 'en_attente';     // Form filled, awaiting Stripe redirect
    public const STATUS_PAID = 'acompte_payé';      // Deposit paid via Stripe
    public const STATUS_CONFIRMED = 'confirmé';     // Photographer confirmed the date
    public const STATUS_CANCELLED = 'annulé';
    public const STATUS_REFUNDED = 'remboursé';

    public const STATUSES = [
        'En attente' => self::STATUS_PENDING,
        'Acompte payé' => self::STATUS_PAID,
        'Confirmé' => self::STATUS_CONFIRMED,
        'Annulé' => self::STATUS_CANCELLED,
        'Remboursé' => self::STATUS_REFUNDED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $reference = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $service = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $clientName = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $clientEmail = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $clientPhone = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\GreaterThan('today', message: 'La date doit être dans le futur.')]
    private ?\DateTimeImmutable $eventDate = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    private ?string $amountTotal = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    private ?string $depositAmount = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePaymentIntentId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->reference = 'B-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    public function getId(): ?int { return $this->id; }
    public function getReference(): ?string { return $this->reference; }

    public function getService(): ?Service { return $this->service; }
    public function setService(?Service $service): static { $this->service = $service; return $this; }

    public function getClientName(): ?string { return $this->clientName; }
    public function setClientName(string $clientName): static { $this->clientName = $clientName; return $this; }

    public function getClientEmail(): ?string { return $this->clientEmail; }
    public function setClientEmail(string $clientEmail): static { $this->clientEmail = $clientEmail; return $this; }

    public function getClientPhone(): ?string { return $this->clientPhone; }
    public function setClientPhone(?string $clientPhone): static { $this->clientPhone = $clientPhone; return $this; }

    public function getEventDate(): ?\DateTimeImmutable { return $this->eventDate; }
    public function setEventDate(?\DateTimeImmutable $eventDate): static { $this->eventDate = $eventDate; return $this; }

    public function getLocation(): ?string { return $this->location; }
    public function setLocation(?string $location): static { $this->location = $location; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }

    public function getAmountTotal(): ?string { return $this->amountTotal; }
    public function setAmountTotal(string $amountTotal): static { $this->amountTotal = $amountTotal; return $this; }

    public function getDepositAmount(): ?string { return $this->depositAmount; }
    public function setDepositAmount(string $depositAmount): static { $this->depositAmount = $depositAmount; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getStripeSessionId(): ?string { return $this->stripeSessionId; }
    public function setStripeSessionId(?string $stripeSessionId): static { $this->stripeSessionId = $stripeSessionId; return $this; }

    public function getStripePaymentIntentId(): ?string { return $this->stripePaymentIntentId; }
    public function setStripePaymentIntentId(?string $stripePaymentIntentId): static { $this->stripePaymentIntentId = $stripePaymentIntentId; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getPaidAt(): ?\DateTimeImmutable { return $this->paidAt; }
    public function setPaidAt(?\DateTimeImmutable $paidAt): static { $this->paidAt = $paidAt; return $this; }

    public function isPaid(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_CONFIRMED], true);
    }

    public function __toString(): string
    {
        return sprintf('%s — %s', $this->reference ?? '?', $this->clientName ?? '?');
    }
}
