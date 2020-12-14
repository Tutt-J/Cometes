<?php

namespace App\Entity;

use App\Repository\ProgramButtonsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProgramButtonsRepository::class)
 */
class ProgramButtons
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $wording;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $teachable_url;

    /**
     * @ORM\ManyToOne(targetEntity=Program::class, inversedBy="programButtons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Program;

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->wording;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProgram(): ?Program
    {
        return $this->Program;
    }

    public function setProgram(?Program $Program): self
    {
        $this->Program = $Program;

        return $this;
    }

    public function getWording(): ?string
    {
        return $this->wording;
    }

    public function setWording(string $wording): self
    {
        $this->wording = $wording;

        return $this;
    }

    public function getTeachableUrl(): ?string
    {
        return $this->teachable_url;
    }

    public function setTeachableUrl(string $teachable_url): self
    {
        $this->teachable_url = $teachable_url;

        return $this;
    }
}
