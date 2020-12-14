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
     * @ORM\ManyToOne(targetEntity=Program::class, inversedBy="programButtons")
     */
    private $Program;

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
}
