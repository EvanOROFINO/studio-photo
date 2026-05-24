<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 60)]
    private ?string $name = null;

    #[ORM\Column(length: 80, unique: true)]
    private ?string $slug = null;

    /** @var Collection<int, Photo> */
    #[ORM\ManyToMany(targetEntity: Photo::class, mappedBy: 'tags')]
    private Collection $photos;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
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

    /** @return Collection<int, Photo> */
    public function getPhotos(): Collection { return $this->photos; }

    public function __toString(): string { return $this->name ?? ''; }
}
