<?php

namespace App\Entity;

use App\Repository\ContactRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRequestRepository::class)]
class ContactRequest
{
    public const STATUS_NEW = 'nouveau';
    public const STATUS_READ = 'lu';
    public const STATUS_REPLIED = 'traité';

    public const TYPES = [
        'Mariage' => 'mariage',
        'Portrait / Famille' => 'portrait',
        'Événement professionnel' => 'pro',
        'Grossesse / Nouveau-né' => 'grossesse',
        'Autre' => 'autre',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Votre nom est requis.')]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $fullName = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Un email valide est requis.')]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    private ?string $phone = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: 'getTypeChoices')]
    private ?string $projectType = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $eventDate = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Décrivez brièvement votre projet.')]
    #[Assert\Length(min: 10, max: 3000)]
    private ?string $message = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_NEW;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function getTypeChoices(): array
    {
        return array_values(self::TYPES);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getProjectType(): ?string
    {
        return $this->projectType;
    }

    public function setProjectType(string $projectType): static
    {
        $this->projectType = $projectType;
        return $this;
    }

    public function getEventDate(): ?\DateTimeImmutable
    {
        return $this->eventDate;
    }

    public function setEventDate(?\DateTimeImmutable $eventDate): static
    {
        $this->eventDate = $eventDate;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->fullName ?? '?', $this->projectType ?? '?');
    }
}
