<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserFile", mappedBy="userId")
     */
    private $userFiles;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $userType;

    private $userPossibleValues = ['patient', 'doctor'];

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\DoctorToPatient", mappedBy="patientId")
     */
    private $doctorToPatients;

    public function __construct()
    {
        $this->userFiles = new ArrayCollection();
        $this->doctorToPatients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|UserFile[]
     */
    public function getUserFiles(): Collection
    {
        return $this->userFiles;
    }

    public function addUserFile(UserFile $userFile): self
    {
        if (!$this->userFiles->contains($userFile)) {
            $this->userFiles[] = $userFile;
            $userFile->setUserId($this);
        }

        return $this;
    }

    public function removeUserFile(UserFile $userFile): self
    {
        if ($this->userFiles->contains($userFile)) {
            $this->userFiles->removeElement($userFile);
            // set the owning side to null (unless already changed)
            if ($userFile->getUserId() === $this) {
                $userFile->setUserId(null);
            }
        }

        return $this;
    }

    public function getUserType(): ?string
    {
        return $this->userType;
    }

    public function setUserType(string $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function getAllUserPossibleValues()
    {
        $userTypes = [];
        foreach ($this->userPossibleValues as $val) {
            $userTypes[$val] = $val;
        }
        return $userTypes;
    }

    /**
     * @return Collection|DoctorToPatient[]
     */
    public function getDoctorToPatients(): Collection
    {
        return $this->doctorToPatients;
    }

    public function addDoctorToPatient(DoctorToPatient $doctorToPatient): self
    {
        if (!$this->doctorToPatients->contains($doctorToPatient)) {
            $this->doctorToPatients[] = $doctorToPatient;
            $doctorToPatient->addPatientId($this);
        }

        return $this;
    }

    public function removeDoctorToPatient(DoctorToPatient $doctorToPatient): self
    {
        if ($this->doctorToPatients->contains($doctorToPatient)) {
            $this->doctorToPatients->removeElement($doctorToPatient);
            $doctorToPatient->removePatientId($this);
        }

        return $this;
    }


}
