<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    #[Assert\GreaterThan('today')]
    private ?\DateTimeInterface $eventDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $location = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?int $maxParticipants = null;

    #[ORM\ManyToOne(inversedBy: 'organizedEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    #[ORM\Column]
    private bool $isPublished = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Registration::class, cascade: ['remove'])]
    private Collection $registrations;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getEventDate(): ?\DateTimeInterface { return $this->eventDate; }
    public function setEventDate(\DateTimeInterface $eventDate): static { $this->eventDate = $eventDate; return $this; }

    public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }
    public function setEndDate(?\DateTimeInterface $endDate): static { $this->endDate = $endDate; return $this; }

    public function getLocation(): ?string { return $this->location; }
    public function setLocation(string $location): static { $this->location = $location; return $this; }

    public function getMaxParticipants(): ?int { return $this->maxParticipants; }
    public function setMaxParticipants(int $maxParticipants): static { $this->maxParticipants = $maxParticipants; return $this; }

    public function getOrganizer(): ?User { return $this->organizer; }
    public function setOrganizer(?User $organizer): static { $this->organizer = $organizer; return $this; }

    public function isPublished(): bool { return $this->isPublished; }
    public function setIsPublished(bool $isPublished): static { $this->isPublished = $isPublished; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getRegistrations(): Collection { return $this->registrations; }

    public function getConfirmedParticipantsCount(): int
    {
        return $this->registrations->filter(fn($r) => $r->getStatus() === 'confirmed')->count();
    }

    public function getRemainingPlaces(): int
    {
        return max(0, $this->maxParticipants - $this->getConfirmedParticipantsCount());
    }

    public function isFull(): bool
    {
        return $this->getConfirmedParticipantsCount() >= $this->maxParticipants;
    }
}
