<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
#[ORM\Table(name: 'site')]
class Site
{
    public const SLUG_PHOTO = 'photo';
    public const SLUG_VIDEO = 'video';

    public const SLUGS = [
        'Studio Photo' => self::SLUG_PHOTO,
        'Studio Vidéo' => self::SLUG_VIDEO,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: 'getValidSlugs')]
    private ?string $slug = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $domain = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $domainStaging = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $tagline = null;

    #[ORM\Column(length: 7)]
    private string $primaryColor = '#1a1a1a';

    #[ORM\Column(length: 7)]
    private string $accentColor = '#c8a97e';

    #[ORM\Column(length: 50)]
    private string $iconEmoji = '📸';

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isDefault = false;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function getValidSlugs(): array
    {
        return array_values(self::SLUGS);
    }

    public function isPhotoSite(): bool
    {
        return $this->slug === self::SLUG_PHOTO;
    }

    public function isVideoSite(): bool
    {
        return $this->slug === self::SLUG_VIDEO;
    }

    public function getId(): ?int { return $this->id; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getDomain(): ?string { return $this->domain; }
    public function setDomain(string $domain): static { $this->domain = $domain; return $this; }

    public function getDomainStaging(): ?string { return $this->domainStaging; }
    public function setDomainStaging(?string $domainStaging): static { $this->domainStaging = $domainStaging; return $this; }

    public function getTagline(): ?string { return $this->tagline; }
    public function setTagline(?string $tagline): static { $this->tagline = $tagline; return $this; }

    public function getPrimaryColor(): string { return $this->primaryColor; }
    public function setPrimaryColor(string $primaryColor): static { $this->primaryColor = $primaryColor; return $this; }

    public function getAccentColor(): string { return $this->accentColor; }
    public function setAccentColor(string $accentColor): static { $this->accentColor = $accentColor; return $this; }

    public function getIconEmoji(): string { return $this->iconEmoji; }
    public function setIconEmoji(string $iconEmoji): static { $this->iconEmoji = $iconEmoji; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function isDefault(): bool { return $this->isDefault; }
    public function setIsDefault(bool $isDefault): static { $this->isDefault = $isDefault; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
