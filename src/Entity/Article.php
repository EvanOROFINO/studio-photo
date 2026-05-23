<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 200)]
    private ?string $title = null;

    #[ORM\Column(length: 220, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 20, max: 500)]
    private ?string $excerpt = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 100)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImageName = null;

    #[Vich\UploadableField(mapping: 'article_covers', fileNameProperty: 'coverImageName')]
    #[Assert\Image(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]
    private ?File $coverImageFile = null;

    #[ORM\Column(length: 160, nullable: true)]
    #[Assert\Length(max: 160)]
    private ?string $metaTitle = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Assert\Length(max: 200)]
    private ?string $metaDescription = null;

    #[ORM\Column]
    private bool $published = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    private int $viewCount = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ArticleCategory $category = null;

    #[ORM\ManyToOne]
    private ?User $author = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateSlugAndDates(): void
    {
        if ($this->title) {
            $this->slug = (new AsciiSlugger())->slug($this->title)->lower()->toString();
        }
        $this->updatedAt = new \DateTimeImmutable();

        if ($this->published && $this->publishedAt === null) {
            $this->publishedAt = new \DateTimeImmutable();
        }
    }

    public function incrementViewCount(): void
    {
        $this->viewCount++;
    }

    public function getReadingTimeMinutes(): int
    {
        $words = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($words / 200));
    }

    public function getEffectiveMetaTitle(): string
    {
        return $this->metaTitle ?: $this->title ?? '';
    }

    public function getEffectiveMetaDescription(): string
    {
        return $this->metaDescription ?: $this->excerpt ?? '';
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function getExcerpt(): ?string { return $this->excerpt; }
    public function setExcerpt(string $excerpt): static { $this->excerpt = $excerpt; return $this; }
    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }
    public function getCoverImageName(): ?string { return $this->coverImageName; }
    public function setCoverImageName(?string $coverImageName): static { $this->coverImageName = $coverImageName; return $this; }
    public function getCoverImageFile(): ?File { return $this->coverImageFile; }
    public function setCoverImageFile(?File $coverImageFile = null): static
    {
        $this->coverImageFile = $coverImageFile;
        if ($coverImageFile !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }
    public function getMetaTitle(): ?string { return $this->metaTitle; }
    public function setMetaTitle(?string $metaTitle): static { $this->metaTitle = $metaTitle; return $this; }
    public function getMetaDescription(): ?string { return $this->metaDescription; }
    public function setMetaDescription(?string $metaDescription): static { $this->metaDescription = $metaDescription; return $this; }
    public function isPublished(): bool { return $this->published; }
    public function setPublished(bool $published): static { $this->published = $published; return $this; }
    public function getPublishedAt(): ?\DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static { $this->publishedAt = $publishedAt; return $this; }
    public function getViewCount(): int { return $this->viewCount; }
    public function setViewCount(int $viewCount): static { $this->viewCount = $viewCount; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function getCategory(): ?ArticleCategory { return $this->category; }
    public function setCategory(?ArticleCategory $category): static { $this->category = $category; return $this; }
    public function getAuthor(): ?User { return $this->author; }
    public function setAuthor(?User $author): static { $this->author = $author; return $this; }

    public function __toString(): string { return $this->title ?? ''; }
}
