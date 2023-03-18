<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $finishAt = null;

    #[ORM\Column(type: Types::DECIMAL)]
    private ?string $rentAmount = null;

    #[ORM\Column(type: Types::DECIMAL)]
    private ?string $guaranteeAmount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $yousignSignatureId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $yousignDocumentId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $yousignSignerId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $originalPdfFilename = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isSigned = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
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

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getFinishAt(): ?\DateTimeImmutable
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTimeImmutable $finishAt): self
    {
        $this->finishAt = $finishAt;

        return $this;
    }

    public function getRentAmount(): ?string
    {
        return $this->rentAmount;
    }

    public function setRentAmount(string $rentAmount): self
    {
        $this->rentAmount = $rentAmount;

        return $this;
    }

    public function getGuaranteeAmount(): ?string
    {
        return $this->guaranteeAmount;
    }

    public function setGuaranteeAmount(string $guaranteeAmount): self
    {
        $this->guaranteeAmount = $guaranteeAmount;

        return $this;
    }

    public function getYousignSignatureId(): ?string
    {
        return $this->yousignSignatureId;
    }

    public function setYousignSignatureId(?string $yousignSignatureId): self
    {
        $this->yousignSignatureId = $yousignSignatureId;

        return $this;
    }

    public function getYousignDocumentId(): ?string
    {
        return $this->yousignDocumentId;
    }

    public function setYousignDocumentId(?string $yousignDocumentId): self
    {
        $this->yousignDocumentId = $yousignDocumentId;

        return $this;
    }

    public function getYousignSignerId(): ?string
    {
        return $this->yousignSignerId;
    }

    public function setYousignSignerId(?string $yousignSignerId): self
    {
        $this->yousignSignerId = $yousignSignerId;

        return $this;
    }

    public function getOriginalPdfFilename(): ?string
    {
        return $this->originalPdfFilename;
    }

    public function setOriginalPdfFilename(?string $originalPdfFilename): self
    {
        $this->originalPdfFilename = $originalPdfFilename;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isIsSigned(): ?bool
    {
        return $this->isSigned;
    }

    public function setIsSigned(bool $isSigned): self
    {
        $this->isSigned = $isSigned;

        return $this;
    }
}
