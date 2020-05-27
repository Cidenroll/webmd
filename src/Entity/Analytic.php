<?php

namespace App\Entity;

use App\Repository\AnalyticRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnalyticRepository::class)
 */
class Analytic
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $currentUserId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $currentUserMail;

    /**
     * @ORM\Column(type="string", length=750)
     */
    private $action;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $currentRoute;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $userRole;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $userTrace;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrentUserId(): ?int
    {
        return $this->currentUserId;
    }

    public function setCurrentUserId(int $currentUserId): self
    {
        $this->currentUserId = $currentUserId;

        return $this;
    }

    public function getCurrentUserMail(): ?string
    {
        return $this->currentUserMail;
    }

    public function setCurrentUserMail(string $currentUserMail): self
    {
        $this->currentUserMail = $currentUserMail;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getCurrentRoute(): ?string
    {
        return $this->currentRoute;
    }

    public function setCurrentRoute(string $currentRoute): self
    {
        $this->currentRoute = $currentRoute;

        return $this;
    }

    public function getUserRole(): ?string
    {
        return $this->userRole;
    }

    public function setUserRole(string $userRole): self
    {
        $this->userRole = $userRole;

        return $this;
    }

    public function getUserTrace(): ?string
    {
        return $this->userTrace;
    }

    public function setUserTrace(string $userTrace): self
    {
        $this->userTrace = $userTrace;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
