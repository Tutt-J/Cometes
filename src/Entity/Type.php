<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=TypeRepository::class)
 */
class Type
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean")
     */
    private $forEvent;

    /**
     * @ORM\Column(type="boolean")
     */
    private $forContent;

    /**
     * @ORM\OneToMany(targetEntity=Content::class, mappedBy="Type")
     */
    private $contents;

    /**
     * @ORM\Column(type="boolean")
     */
    private $forOpinion;

    /**
     * @ORM\OneToMany(targetEntity=Opinion::class, mappedBy="type")
     */
    private $opinions;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Wording;

    /**
     * @ORM\ManyToMany(targetEntity=Event::class, mappedBy="Type")
     */
    private $events;

    public function __construct()
    {
        $this->contents = new ArrayCollection();
        $this->opinions = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getForEvent(): ?bool
    {
        return $this->forEvent;
    }

    public function setForEvent(bool $forEvent): self
    {
        $this->forEvent = $forEvent;

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

    public function getForContent(): ?bool
    {
        return $this->forContent;
    }

    public function setForContent(bool $forContent): self
    {
        $this->forContent = $forContent;

        return $this;
    }

    /**
     * @return Collection|Content[]
     */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContent(Content $content): self
    {
        if (!$this->contents->contains($content)) {
            $this->contents[] = $content;
            $content->setType($this);
        }

        return $this;
    }

    public function removeContent(Content $content): self
    {
        if ($this->contents->contains($content)) {
            $this->contents->removeElement($content);
            // set the owning side to null (unless already changed)
            if ($content->getType() === $this) {
                $content->setType(null);
            }
        }

        return $this;
    }

    public function getForOpinion(): ?bool
    {
        return $this->forOpinion;
    }

    public function setForOpinion(bool $forOpinion): self
    {
        $this->forOpinion = $forOpinion;

        return $this;
    }

    /**
     * @return Collection|Opinion[]
     */
    public function getOpinions(): Collection
    {
        return $this->opinions;
    }

    public function addOpinion(Opinion $opinion): self
    {
        if (!$this->opinions->contains($opinion)) {
            $this->opinions[] = $opinion;
            $opinion->setType($this);
        }

        return $this;
    }

    public function removeOpinion(Opinion $opinion): self
    {
        if ($this->opinions->removeElement($opinion)) {
            // set the owning side to null (unless already changed)
            if ($opinion->getType() === $this) {
                $opinion->setType(null);
            }
        }

        return $this;
    }

    public function getWording(): ?string
    {
        return $this->Wording;
    }

    public function setWording(string $Wording): self
    {
        $this->Wording = $Wording;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->addType($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            $event->removeType($this);
        }

        return $this;
    }
}
