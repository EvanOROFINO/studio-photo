<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'shop_order')]
class Order
{
    public const STATUS_PENDING = 'en_attente';
    public const STATUS_PAID = 'payée';
    public const STATUS_SHIPPED = 'expédiée';
    public const STATUS_DELIVERED = 'livrée';
    public const STATUS_CANCELLED = 'annulée';
    public const STATUS_REFUNDED = 'remboursée';

    public const STATUSES = [
        'En attente' => self::STATUS_PENDING,
        'Payée' => self::STATUS_PAID,
        'Expédiée' => self::STATUS_SHIPPED,
        'Livrée' => self::STATUS_DELIVERED,
        'Annulée' => self::STATUS_CANCELLED,
        'Remboursée' => self::STATUS_REFUNDED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $customerName = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $customerEmail = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $customerPhone = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $shippingAddress = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private ?string $shippingZip = null;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank]
    private ?string $shippingCity = null;

    #[ORM\Column(length: 80)]
    private string $shippingCountry = 'France';

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    private ?string $subtotal = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    private string $shippingFee = '8.00';

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    private ?string $totalAmount = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePaymentIntentId = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $shippedAt = null;

    /** @var Collection<int, OrderItem> */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->reference = 'O-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $this->items = new ArrayCollection();
    }

    public function recalculateTotals(): void
    {
        $subtotal = 0.0;
        foreach ($this->items as $item) {
            $subtotal += $item->getLineTotal();
        }
        $this->subtotal = (string) round($subtotal, 2);
        $this->totalAmount = (string) round($subtotal + (float) $this->shippingFee, 2);
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_SHIPPED, self::STATUS_DELIVERED], true);
    }

    public function getTotalQuantity(): int
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getQuantity();
        }
        return $total;
    }

    public function getId(): ?int { return $this->id; }
    public function getReference(): ?string { return $this->reference; }

    public function getCustomerName(): ?string { return $this->customerName; }
    public function setCustomerName(string $customerName): static { $this->customerName = $customerName; return $this; }

    public function getCustomerEmail(): ?string { return $this->customerEmail; }
    public function setCustomerEmail(string $customerEmail): static { $this->customerEmail = $customerEmail; return $this; }

    public function getCustomerPhone(): ?string { return $this->customerPhone; }
    public function setCustomerPhone(?string $customerPhone): static { $this->customerPhone = $customerPhone; return $this; }

    public function getShippingAddress(): ?string { return $this->shippingAddress; }
    public function setShippingAddress(string $shippingAddress): static { $this->shippingAddress = $shippingAddress; return $this; }

    public function getShippingZip(): ?string { return $this->shippingZip; }
    public function setShippingZip(string $shippingZip): static { $this->shippingZip = $shippingZip; return $this; }

    public function getShippingCity(): ?string { return $this->shippingCity; }
    public function setShippingCity(string $shippingCity): static { $this->shippingCity = $shippingCity; return $this; }

    public function getShippingCountry(): string { return $this->shippingCountry; }
    public function setShippingCountry(string $shippingCountry): static { $this->shippingCountry = $shippingCountry; return $this; }

    public function getSubtotal(): ?string { return $this->subtotal; }
    public function setSubtotal(string $subtotal): static { $this->subtotal = $subtotal; return $this; }

    public function getShippingFee(): string { return $this->shippingFee; }
    public function setShippingFee(string $shippingFee): static { $this->shippingFee = $shippingFee; return $this; }

    public function getTotalAmount(): ?string { return $this->totalAmount; }
    public function setTotalAmount(string $totalAmount): static { $this->totalAmount = $totalAmount; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getStripeSessionId(): ?string { return $this->stripeSessionId; }
    public function setStripeSessionId(?string $id): static { $this->stripeSessionId = $id; return $this; }

    public function getStripePaymentIntentId(): ?string { return $this->stripePaymentIntentId; }
    public function setStripePaymentIntentId(?string $id): static { $this->stripePaymentIntentId = $id; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getPaidAt(): ?\DateTimeImmutable { return $this->paidAt; }
    public function setPaidAt(?\DateTimeImmutable $paidAt): static { $this->paidAt = $paidAt; return $this; }

    public function getShippedAt(): ?\DateTimeImmutable { return $this->shippedAt; }
    public function setShippedAt(?\DateTimeImmutable $shippedAt): static { $this->shippedAt = $shippedAt; return $this; }

    /** @return Collection<int, OrderItem> */
    public function getItems(): Collection { return $this->items; }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }
        return $this;
    }

    public function removeItem(OrderItem $item): static
    {
        if ($this->items->removeElement($item) && $item->getOrder() === $this) {
            $item->setOrder(null);
        }
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s — %s', $this->reference ?? '?', $this->customerName ?? '?');
    }
}
