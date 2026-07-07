<?php

namespace App\Entity;

use App\Repository\ClientGalleryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientGalleryRepository::class)]
class ClientGallery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $token = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $clientName = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $clientEmail = null;

    #[ORM\Column]
    private ?string $passwordHash = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $welcomeMessage = null;

    #[ORM\Column]
    private bool $allowDownload = true;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $shootDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastViewedAt = null;

    #[ORM\Column]
    private int $viewCount = 0;

    /** @var Collection<int, ClientPhoto> */
    #[ORM\OneToMany(targetEntity: ClientPhoto::class, mappedBy: 'gallery', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC', 'createdAt' => 'ASC'])]
    private Collection $photos;

    /** @var Collection<int, ClientFilm> */
    #[ORM\OneToMany(targetEntity: ClientFilm::class, mappedBy: 'gallery', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC', 'createdAt' => 'ASC'])]
    private Collection $films;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = (new \DateTimeImmutable())->modify('+6 months');
        $this->token = bin2hex(random_bytes(16));
        $this->photos = new ArrayCollection();
        $this->films = new ArrayCollection();
    }

    public function regenerateToken(): void
    {
        $this->token = bin2hex(random_bytes(16));
    }

    public function isExpired(): bool
    {
        return $this->expiresAt !== null && $this->expiresAt < new \DateTimeImmutable();
    }

    public function isAccessible(): bool
    {
        return $this->active && !$this->isExpired();
    }

    public function registerView(): void
    {
        $this->lastViewedAt = new \DateTimeImmutable();
        $this->viewCount++;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(string $clientName): static
    {
        $this->clientName = $clientName;
        return $this;
    }

    public function getClientEmail(): ?string
    {
        return $this->clientEmail;
    }

    public function setClientEmail(?string $clientEmail): static
    {
        $this->clientEmail = $clientEmail;
        return $this;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function getWelcomeMessage(): ?string
    {
        return $this->welcomeMessage;
    }

    public function setWelcomeMessage(?string $welcomeMessage): static
    {
        $this->welcomeMessage = $welcomeMessage;
        return $this;
    }

    public function isAllowDownload(): bool
    {
        return $this->allowDownload;
    }

    public function setAllowDownload(bool $allowDownload): static
    {
        $this->allowDownload = $allowDownload;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    public function getShootDate(): ?\DateTimeImmutable
    {
        return $this->shootDate;
    }

    public function setShootDate(?\DateTimeImmutable $shootDate): static
    {
        $this->shootDate = $shootDate;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastViewedAt(): ?\DateTimeImmutable
    {
        return $this->lastViewedAt;
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    /** @return Collection<int, ClientPhoto> */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(ClientPhoto $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setGallery($this);
        }
        return $this;
    }

    public function removePhoto(ClientPhoto $photo): static
    {
        if ($this->photos->removeElement($photo) && $photo->getGallery() === $this) {
            $photo->setGallery(null);
        }
        return $this;
    }

    /** @return Collection<int, ClientFilm> */
    public function getFilms(): Collection
    {
        return $this->films;
    }

    public function addFilm(ClientFilm $film): static
    {
        if (!$this->films->contains($film)) {
            $this->films->add($film);
            $film->setGallery($this);
        }
        return $this;
    }

    public function removeFilm(ClientFilm $film): static
    {
        if ($this->films->removeElement($film) && $film->getGallery() === $this) {
            $film->setGallery(null);
        }
        return $this;
    }

    public function hasFilms(): bool
    {
        return !$this->films->isEmpty();
    }

    public function __toString(): string
    {
        return sprintf('%s — %s', $this->title ?? '?', $this->clientName ?? '?');
    }
}
