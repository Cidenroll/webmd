<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DoctorToPatientRepository")
 */
class DoctorToPatient
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
    private $doctorId;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="doctorToPatients")
     */
    private $patientId;

    public function __construct()
    {
        $this->patientId = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoctorId(): ?int
    {
        return $this->doctorId;
    }

    public function setDoctorId(int $doctorId): self
    {
        $this->doctorId = $doctorId;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getPatientId(): Collection
    {
        return $this->patientId;
    }

    public function addPatientId(User $patientId): self
    {
        if (!$this->patientId->contains($patientId)) {
            $this->patientId[] = $patientId;
        }

        return $this;
    }

    public function removePatientId(User $patientId): self
    {
        if ($this->patientId->contains($patientId)) {
            $this->patientId->removeElement($patientId);
        }

        return $this;
    }

    

}
