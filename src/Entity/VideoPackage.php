<?php

namespace App\Entity;

use App\Entity\Trait\SiteAwareTrait;
use App\Repository\VideoPackageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VideoPackageRepository::class)]
#[ORM\Table(name: 'video_package')]
class VideoPackage
{
    use SiteAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $tagline = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero]
    private int $price = 0;

    #[ORM\Column(length: 30)]
    private string $priceSuffix = '€';

    /**
     * One feature per line (textarea in admin), rendered as a bullet list.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $features = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $deliveryTime = null;

    #[ORM\Column]
    private bool $featured = false;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private int $position = 0;

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getTagline(): ?string { return $this->tagline; }
    public function setTagline(?string $tagline): static { $this->tagline = $tagline; return $this; }

    public function getPrice(): int { return $this->price; }
    public function setPrice(int $price): static { $this->price = $price; return $this; }

    public function getPriceSuffix(): string { return $this->priceSuffix; }
    public function setPriceSuffix(string $priceSuffix): static { $this->priceSuffix = $priceSuffix; return $this; }

    public function getFeatures(): ?string { return $this->features; }
    public function setFeatures(?string $features): static { $this->features = $features; return $this; }

    /** @return string[] */
    public function getFeatureLines(): array
    {
        if (!$this->features) {
            return [];
        }
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $this->features))));
    }

    public function getDeliveryTime(): ?string { return $this->deliveryTime; }
    public function setDeliveryTime(?string $deliveryTime): static { $this->deliveryTime = $deliveryTime; return $this; }

    public function isFeatured(): bool { return $this->featured; }
    public function setFeatured(bool $featured): static { $this->featured = $featured; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function __toString(): string { return $this->name ?? ''; }
}
