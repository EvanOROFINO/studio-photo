<?php

namespace App\Entity;

use App\Repository\ClientFilmRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A finished film delivered to a client inside a private ClientGallery
 * (e.g. the wedding movie, the corporate aftermovie).
 * Uses a private/unlisted YouTube or Vimeo link.
 */
#[ORM\Entity(repositoryClass: ClientFilmRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'client_film')]
class ClientFilm
{
    public const SOURCE_YOUTUBE = 'youtube';
    public const SOURCE_VIMEO = 'vimeo';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ClientGallery::class, inversedBy: 'films')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClientGallery $gallery = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $url = null;

    #[ORM\Column(length: 20)]
    private string $source = self::SOURCE_VIMEO;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $externalId = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $duration = null;

    /**
     * Optional direct download link (e.g. WeTransfer, S3 signed URL) for the
     * full-resolution file the client can keep.
     */
    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url]
    private ?string $downloadUrl = null;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function parseUrl(): void
    {
        if (!$this->url) {
            return;
        }
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $this->url, $m)) {
            $this->source = self::SOURCE_YOUTUBE;
            $this->externalId = $m[1];
            return;
        }
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

    public function getGallery(): ?ClientGallery { return $this->gallery; }
    public function setGallery(?ClientGallery $gallery): static { $this->gallery = $gallery; return $this; }

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

    public function getDuration(): ?string { return $this->duration; }
    public function setDuration(?string $duration): static { $this->duration = $duration; return $this; }

    public function getDownloadUrl(): ?string { return $this->downloadUrl; }
    public function setDownloadUrl(?string $downloadUrl): static { $this->downloadUrl = $downloadUrl; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function __toString(): string { return $this->title ?? ''; }
}
