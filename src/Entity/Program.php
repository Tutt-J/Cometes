<?php

namespace App\Entity;

use App\Repository\ProgramRepository;
use App\Service\ContentsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=ProgramRepository::class)
 */
class Program
{
    use TimestampableEntity;
    use ContentsTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_online;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string", length=255)
     */
    private $slug;


    /**
     * @ORM\ManyToOne(targetEntity=Image::class, inversedBy="programs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $img;

    /**
     * @ORM\ManyToMany(targetEntity=TypeProgram::class, inversedBy="programs")
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=ProgramButtons::class, mappedBy="Program", orphanRemoval=true, fetch="EAGER")
     */
    private $programButtons;

    public function __construct()
    {
        $this->type = new ArrayCollection();
        $this->programButtons = new ArrayCollection();
    }


    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }


    public function getIsOnline(): ?bool
    {
        return $this->is_online;
    }

    public function setIsOnline(bool $is_online): self
    {
        $this->is_online = $is_online;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }


    public function getImg(): ?Image
    {
        return $this->img;
    }

    public function setImg(?Image $img): self
    {
        $this->img = $img;

        return $this;
    }

    /**
     * @return Collection|TypeProgram[]
     */
    public function getType(): Collection
    {
        return $this->type;
    }

    public function addType(TypeProgram $type): self
    {
        if (!$this->type->contains($type)) {
            $this->type[] = $type;
        }

        return $this;
    }

    public function removeType(TypeProgram $type): self
    {
        $this->type->removeElement($type);

        return $this;
    }

    /**
     * @return Collection|ProgramButtons[]
     */
    public function getProgramButtons(): Collection
    {
        return $this->programButtons;
    }

    public function addProgramButton(ProgramButtons $programButton): self
    {
        if (!$this->programButtons->contains($programButton)) {
            $this->programButtons[] = $programButton;
            $programButton->setProgram($this);
        }

        return $this;
    }

    public function removeProgramButton(ProgramButtons $programButton): self
    {
        if ($this->programButtons->removeElement($programButton) && $programButton->getProgram() === $this) {
                $programButton->setProgram(null);
        }

        return $this;
    }
}
