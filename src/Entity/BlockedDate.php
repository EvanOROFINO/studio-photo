<?php

namespace App\Entity;

use App\Repository\BlockedDateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BlockedDateRepository::class)]
class BlockedDate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Expression(
        "value === null or value >= this.getStartDate()",
        message: "La date de fin doit être après la date de début."
    )]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    private ?string $reason = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getStartDate(): ?\DateTimeImmutable { return $this->startDate; }
    public function setStartDate(\DateTimeImmutable $startDate): static { $this->startDate = $startDate; return $this; }

    public function getEndDate(): ?\DateTimeImmutable { return $this->endDate; }
    public function setEndDate(?\DateTimeImmutable $endDate): static { $this->endDate = $endDate; return $this; }

    /** Returns the effective last day (= startDate if endDate is null) */
    public function getEffectiveEndDate(): \DateTimeImmutable
    {
        return $this->endDate ?? $this->startDate;
    }

    public function getReason(): ?string { return $this->reason; }
    public function setReason(string $reason): static { $this->reason = $reason; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function isSingleDay(): bool
    {
        return $this->endDate === null || $this->endDate->format('Y-m-d') === $this->startDate->format('Y-m-d');
    }

    public function __toString(): string
    {
        if ($this->isSingleDay()) {
            return sprintf('%s — %s', $this->startDate?->format('d/m/Y'), $this->reason);
        }
        return sprintf('%s → %s — %s', $this->startDate?->format('d/m/Y'), $this->endDate?->format('d/m/Y'), $this->reason);
    }
}
