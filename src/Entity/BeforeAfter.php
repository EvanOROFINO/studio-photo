<?php

namespace App\Entity;

use App\Repository\BeforeAfterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: BeforeAfterRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class BeforeAfter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $beforeImageName = null;

    #[Vich\UploadableField(mapping: 'before_after', fileNameProperty: 'beforeImageName')]
    #[Assert\Image(maxSize: '10M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]
    private ?File $beforeImageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $afterImageName = null;

    #[Vich\UploadableField(mapping: 'before_after', fileNameProperty: 'afterImageName')]
    #[Assert\Image(maxSize: '10M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]
    private ?File $afterImageFile = null;

    #[ORM\Column]
    private bool $published = true;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getBeforeImageName(): ?string { return $this->beforeImageName; }
    public function setBeforeImageName(?string $beforeImageName): static { $this->beforeImageName = $beforeImageName; return $this; }

    public function getBeforeImageFile(): ?File { return $this->beforeImageFile; }
    public function setBeforeImageFile(?File $f = null): static
    {
        $this->beforeImageFile = $f;
        if ($f !== null) { $this->updatedAt = new \DateTimeImmutable(); }
        return $this;
    }

    public function getAfterImageName(): ?string { return $this->afterImageName; }
    public function setAfterImageName(?string $afterImageName): static { $this->afterImageName = $afterImageName; return $this; }

    public function getAfterImageFile(): ?File { return $this->afterImageFile; }
    public function setAfterImageFile(?File $f = null): static
    {
        $this->afterImageFile = $f;
        if ($f !== null) { $this->updatedAt = new \DateTimeImmutable(); }
        return $this;
    }

    public function isPublished(): bool { return $this->published; }
    public function setPublished(bool $published): static { $this->published = $published; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function __toString(): string { return $this->title ?? ''; }
}
