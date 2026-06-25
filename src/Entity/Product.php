<?php

namespace App\Entity;

use App\Entity\Trait\SiteAwareTrait;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Product
{
    use SiteAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 150)]
    private ?string $title = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 20, max: 2000)]
    private ?string $description = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $format = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    #[Assert\Positive]
    private ?string $price = null;

    /** -1 = unlimited / print-on-demand */
    #[ORM\Column]
    private int $stock = -1;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'products', fileNameProperty: 'imageName')]
    #[Assert\Image(maxSize: '8M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]
    private ?File $imageFile = null;

    #[ORM\Column]
    private bool $featured = false;

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

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateSlugAndTimestamp(): void
    {
        if ($this->title) {
            $this->slug = (new AsciiSlugger())->slug($this->title)->lower()->toString();
        }
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isInStock(): bool
    {
        return $this->stock === -1 || $this->stock > 0;
    }

    public function isPrintOnDemand(): bool
    {
        return $this->stock === -1;
    }

    public function decreaseStock(int $quantity = 1): void
    {
        if ($this->stock > 0) {
            $this->stock = max(0, $this->stock - $quantity);
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getFormat(): ?string { return $this->format; }
    public function setFormat(?string $format): static { $this->format = $format; return $this; }
    public function getPrice(): ?string { return $this->price; }
    public function setPrice(string $price): static { $this->price = $price; return $this; }
    public function getPriceAsFloat(): float { return (float) $this->price; }
    public function getStock(): int { return $this->stock; }
    public function setStock(int $stock): static { $this->stock = $stock; return $this; }
    public function getImageName(): ?string { return $this->imageName; }
    public function setImageName(?string $imageName): static { $this->imageName = $imageName; return $this; }
    public function getImageFile(): ?File { return $this->imageFile; }
    public function setImageFile(?File $f = null): static
    {
        $this->imageFile = $f;
        if ($f !== null) { $this->updatedAt = new \DateTimeImmutable(); }
        return $this;
    }
    public function isFeatured(): bool { return $this->featured; }
    public function setFeatured(bool $featured): static { $this->featured = $featured; return $this; }
    public function isPublished(): bool { return $this->published; }
    public function setPublished(bool $published): static { $this->published = $published; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function __toString(): string { return $this->title ?? ''; }
}
