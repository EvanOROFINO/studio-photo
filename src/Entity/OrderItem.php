<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'shop_order_item')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Order $order = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Product $product = null;

    /** Snapshot of the product name at the time of purchase. */
    #[ORM\Column(length: 200)]
    private ?string $productTitle = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $productFormat = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\Column]
    private int $quantity = 1;

    public function getLineTotal(): float
    {
        return (float) $this->unitPrice * $this->quantity;
    }

    public function getId(): ?int { return $this->id; }

    public function getOrder(): ?Order { return $this->order; }
    public function setOrder(?Order $order): static { $this->order = $order; return $this; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): static { $this->product = $product; return $this; }

    public function getProductTitle(): ?string { return $this->productTitle; }
    public function setProductTitle(string $productTitle): static { $this->productTitle = $productTitle; return $this; }

    public function getProductFormat(): ?string { return $this->productFormat; }
    public function setProductFormat(?string $productFormat): static { $this->productFormat = $productFormat; return $this; }

    public function getUnitPrice(): ?string { return $this->unitPrice; }
    public function setUnitPrice(string $unitPrice): static { $this->unitPrice = $unitPrice; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }
}
