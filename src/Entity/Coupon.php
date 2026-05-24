<?php

namespace App\Entity;

use App\Repository\CouponRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
#[UniqueEntity(fields: ['code'], message: 'Ce code promo existe déjà.')]
class Coupon
{
    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';

    public const TYPES = [
        'Pourcentage (%)' => self::TYPE_PERCENT,
        'Montant fixe (€)' => self::TYPE_FIXED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 40)]
    #[Assert\Regex('/^[A-Z0-9_-]+$/', message: 'Uniquement lettres majuscules, chiffres, _ et -')]
    private ?string $code = null;

    #[ORM\Column(length: 20)]
    private string $type = self::TYPE_PERCENT;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    #[Assert\Positive]
    private ?string $value = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $minAmount = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxUses = null;

    #[ORM\Column]
    private int $usedCount = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $validFrom = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $validUntil = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function isUsable(float $cartSubtotal): bool
    {
        if (!$this->active) {
            return false;
        }
        $now = new \DateTimeImmutable();
        if ($this->validFrom && $now < $this->validFrom) {
            return false;
        }
        if ($this->validUntil && $now > $this->validUntil) {
            return false;
        }
        if ($this->maxUses !== null && $this->usedCount >= $this->maxUses) {
            return false;
        }
        if ($this->minAmount !== null && $cartSubtotal < (float) $this->minAmount) {
            return false;
        }
        return true;
    }

    public function getNotUsableReason(float $cartSubtotal): ?string
    {
        if (!$this->active) {
            return 'Ce code n\'est plus actif.';
        }
        $now = new \DateTimeImmutable();
        if ($this->validFrom && $now < $this->validFrom) {
            return 'Ce code n\'est pas encore valide.';
        }
        if ($this->validUntil && $now > $this->validUntil) {
            return 'Ce code a expiré.';
        }
        if ($this->maxUses !== null && $this->usedCount >= $this->maxUses) {
            return 'Ce code a déjà été utilisé le maximum de fois.';
        }
        if ($this->minAmount !== null && $cartSubtotal < (float) $this->minAmount) {
            return sprintf(
                'Montant minimum requis : %s €.',
                number_format((float) $this->minAmount, 0, ',', ' '),
            );
        }
        return null;
    }

    public function computeDiscount(float $cartSubtotal): float
    {
        $value = (float) $this->value;
        $discount = match ($this->type) {
            self::TYPE_PERCENT => round($cartSubtotal * $value / 100, 2),
            self::TYPE_FIXED => $value,
            default => 0.0,
        };
        // Never let the discount exceed the subtotal
        return min($discount, $cartSubtotal);
    }

    public function incrementUsedCount(): void
    {
        $this->usedCount++;
    }

    public function getHumanLabel(): string
    {
        if ($this->type === self::TYPE_PERCENT) {
            return sprintf('-%s%%', (int) $this->value);
        }
        return sprintf('-%s €', number_format((float) $this->value, 0, ',', ' '));
    }

    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = strtoupper($code); return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getValue(): ?string { return $this->value; }
    public function setValue(string $value): static { $this->value = $value; return $this; }
    public function getMinAmount(): ?string { return $this->minAmount; }
    public function setMinAmount(?string $minAmount): static { $this->minAmount = $minAmount; return $this; }
    public function getMaxUses(): ?int { return $this->maxUses; }
    public function setMaxUses(?int $maxUses): static { $this->maxUses = $maxUses; return $this; }
    public function getUsedCount(): int { return $this->usedCount; }
    public function setUsedCount(int $usedCount): static { $this->usedCount = $usedCount; return $this; }
    public function getValidFrom(): ?\DateTimeImmutable { return $this->validFrom; }
    public function setValidFrom(?\DateTimeImmutable $validFrom): static { $this->validFrom = $validFrom; return $this; }
    public function getValidUntil(): ?\DateTimeImmutable { return $this->validUntil; }
    public function setValidUntil(?\DateTimeImmutable $validUntil): static { $this->validUntil = $validUntil; return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): static { $this->active = $active; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function __toString(): string { return $this->code ?? ''; }
}
