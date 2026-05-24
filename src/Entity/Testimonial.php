<?php

namespace App\Entity;

use App\Repository\TestimonialRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: TestimonialRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Testimonial
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $authorName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $authorRole = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatarName = null;

    #[Vich\UploadableField(mapping: 'testimonial_avatars', fileNameProperty: 'avatarName')]
    #[Assert\Image(maxSize: '3M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]
    private ?File $avatarFile = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 20, max: 1000)]
    private ?string $content = null;

    #[ORM\Column]
    #[Assert\Range(min: 1, max: 5)]
    private int $rating = 5;

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

    public function getInitials(): string
    {
        $parts = preg_split('/\s+/', trim($this->authorName ?? ''));
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            if ($p !== '') {
                $initials .= mb_strtoupper(mb_substr($p, 0, 1));
            }
        }
        return $initials ?: '?';
    }

    public function getId(): ?int { return $this->id; }

    public function getAuthorName(): ?string { return $this->authorName; }
    public function setAuthorName(string $authorName): static { $this->authorName = $authorName; return $this; }

    public function getAuthorRole(): ?string { return $this->authorRole; }
    public function setAuthorRole(?string $authorRole): static { $this->authorRole = $authorRole; return $this; }

    public function getAvatarName(): ?string { return $this->avatarName; }
    public function setAvatarName(?string $avatarName): static { $this->avatarName = $avatarName; return $this; }

    public function getAvatarFile(): ?File { return $this->avatarFile; }
    public function setAvatarFile(?File $avatarFile = null): static
    {
        $this->avatarFile = $avatarFile;
        if ($avatarFile !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }

    public function getRating(): int { return $this->rating; }
    public function setRating(int $rating): static { $this->rating = $rating; return $this; }

    public function isPublished(): bool { return $this->published; }
    public function setPublished(bool $published): static { $this->published = $published; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function __toString(): string { return $this->authorName ?? ''; }
}
