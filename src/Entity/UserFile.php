<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserFileRepository")
 *
 */
class UserFile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userFiles")
     */
    private $userId;

    private $avaiableDocTypes = ['Annual checkup', 'Medical report'];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $docType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $doctorId;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Comment;

    /**
     * @ORM\Column(type="blob")
     */
    private $fileContent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getDocType(): ?string
    {
        return $this->docType;
    }

    public function setDocType(string $docType): self
    {
        $this->docType = $docType;

        return $this;
    }

    public function getAvailableDocTypes()
    {
        $docTypes = [];
        foreach ($this->avaiableDocTypes as $docType) {
            $docTypes[$docType] = $docType;
        }
        return $docTypes;
    }

    public function getDoctorId(): ?int
    {
        return $this->doctorId;
    }

    public function setDoctorId(?int $doctorId): self
    {
        $this->doctorId = $doctorId;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->Comment;
    }

    public function setComment(?string $Comment): self
    {
        $this->Comment = $Comment;

        return $this;
    }

    public function getFileContent()
    {
        return $this->fileContent;
    }

    public function setFileContent($fileContent): self
    {
        $this->fileContent = $fileContent;

        return $this;
    }

}
