<?php

namespace App\Entity;

use App\Repository\ArticleCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleCategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ArticleCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 120, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private int $position = 0;

    /** @var Collection<int, Article> */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'category')]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateSlug(): void
    {
        if ($this->name) {
            $this->slug = (new AsciiSlugger())->slug($this->name)->lower()->toString();
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    /** @return Collection<int, Article> */
    public function getArticles(): Collection { return $this->articles; }

    public function __toString(): string { return $this->name ?? ''; }
}
