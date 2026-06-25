<?php

namespace App\Entity;

use App\Entity\Trait\SiteAwareTrait;
use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Video
{
    use SiteAwareTrait;

    public const SOURCE_YOUTUBE = 'youtube';
    public const SOURCE_VIMEO = 'vimeo';

    public const SOURCES = [
        'YouTube' => self::SOURCE_YOUTUBE,
        'Vimeo' => self::SOURCE_VIMEO,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Full URL pasted by the photographer; the externalId is extracted in a hook.
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $url = null;

    #[ORM\Column(length: 20)]
    private string $source = self::SOURCE_YOUTUBE;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $externalId = null;

    #[ORM\Column]
    private bool $featured = false;

    #[ORM\Column]
    private bool $published = true;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * Detect YouTube/Vimeo from the URL and extract the video ID.
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function parseUrl(): void
    {
        if (!$this->url) {
            return;
        }

        // YouTube — handles watch?v=, youtu.be/, embed/, shorts/
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $this->url, $m)) {
            $this->source = self::SOURCE_YOUTUBE;
            $this->externalId = $m[1];
            return;
        }

        // Vimeo — handles vimeo.com/123, vimeo.com/channels/xxx/123
        if (preg_match('~vimeo\.com/(?:channels/[^/]+/|video/)?(\d+)~', $this->url, $m)) {
            $this->source = self::SOURCE_VIMEO;
            $this->externalId = $m[1];
        }
    }

    public function getEmbedUrl(): ?string
    {
        if (!$this->externalId) {
            return null;
        }
        return match ($this->source) {
            self::SOURCE_YOUTUBE => sprintf('https://www.youtube-nocookie.com/embed/%s', $this->externalId),
            self::SOURCE_VIMEO => sprintf('https://player.vimeo.com/video/%s', $this->externalId),
            default => null,
        };
    }

    public function getThumbnailUrl(): ?string
    {
        if (!$this->externalId) {
            return null;
        }
        return match ($this->source) {
            self::SOURCE_YOUTUBE => sprintf('https://img.youtube.com/vi/%s/hqdefault.jpg', $this->externalId),
            self::SOURCE_VIMEO => sprintf('https://vumbnail.com/%s.jpg', $this->externalId),
            default => null,
        };
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getUrl(): ?string { return $this->url; }
    public function setUrl(string $url): static { $this->url = $url; return $this; }

    public function getSource(): string { return $this->source; }
    public function setSource(string $source): static { $this->source = $source; return $this; }

    public function getExternalId(): ?string { return $this->externalId; }
    public function setExternalId(?string $externalId): static { $this->externalId = $externalId; return $this; }

    public function isFeatured(): bool { return $this->featured; }
    public function setFeatured(bool $featured): static { $this->featured = $featured; return $this; }

    public function isPublished(): bool { return $this->published; }
    public function setPublished(bool $published): static { $this->published = $published; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function __toString(): string { return $this->title ?? ''; }
}
