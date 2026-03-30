<?php

namespace App\Entity;

use App\Repository\ConsentLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsentLogRepository::class)]
class ConsentLog
{
    public const ACTION_CONSENT_GIVEN     = 'consent_given';
    public const ACTION_CONSENT_WITHDRAWN = 'consent_withdrawn';
    public const ACTION_DATA_ACCESSED     = 'data_accessed';
    public const ACTION_DATA_DELETED      = 'data_deleted';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'consentLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private ?string $action = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $timestamp = null;

    /** IP address stored as SHA-256 hash (RGPD compliant) */
    #[ORM\Column(length: 64, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $details = null;

    public function __construct()
    {
        $this->timestamp = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getAction(): ?string { return $this->action; }
    public function setAction(string $action): static { $this->action = $action; return $this; }

    public function getTimestamp(): ?\DateTimeImmutable { return $this->timestamp; }
    public function setTimestamp(\DateTimeImmutable $timestamp): static { $this->timestamp = $timestamp; return $this; }

    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ip): static
    {
        // Store as SHA-256 hash (RGPD: IP is personal data)
        $this->ipAddress = $ip ? hash('sha256', $ip) : null;
        return $this;
    }

    public function getDetails(): ?string { return $this->details; }
    public function setDetails(?string $details): static { $this->details = $details; return $this; }
}
