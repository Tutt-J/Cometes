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
     * @ORM\Column(type="string", length=255)
     */
    private $teachable_url;

    /**
     * @ORM\ManyToOne(targetEntity=Image::class, inversedBy="programs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $img;

    /**
     * @ORM\ManyToMany(targetEntity=TypeProgram::class, inversedBy="programs")
     */
    private $type;

    public function __construct()
    {
        $this->type = new ArrayCollection();
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

    public function getTeachableUrl(): ?string
    {
        return $this->teachable_url;
    }

    public function setTeachableUrl(string $teachable_url): self
    {
        $this->teachable_url = $teachable_url;

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
}
