<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subTitle;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Image")
     */
    private $img;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $landingPageUrl;

    /**
     * @ORM\ManyToOne(targetEntity="Type", inversedBy="contents", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="UserEvent", mappedBy="event", fetch="EXTRA_LAZY")
     */
    private $userEvents;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbMinParticipant;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbMaxParticipant;

    /**
     * @ORM\OneToMany(targetEntity=EventPricing::class, mappedBy="event", orphanRemoval=true, fetch="EAGER")
     */
    private $eventPricings;

    /**
     * @ORM\JoinColumn(nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\Address", cascade={"persist"}))
     */
    private $address;

    /**
     * @ORM\Column(type="boolean")
     */
    private $onlineEvent;


    public function __construct()
    {
        $this->userEvents = new ArrayCollection();
        $this->eventPricings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    public function setSubTitle(?string $subTitle): self
    {
        $this->subTitle = $subTitle;

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLandingPageUrl(): ?string
    {
        return $this->landingPageUrl;
    }

    public function setLandingPageUrl(?string $landingPageUrl): self
    {
        $this->landingPageUrl = $landingPageUrl;

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
     * @return Collection|UserEvent[]
     */
    public function getUserEvents(): Collection
    {
        return $this->userEvents;
    }

    public function addUserEvent(UserEvent $userEvent): self
    {
        if (!$this->userEvents->contains($userEvent)) {
            $this->userEvents[] = $userEvent;
            $userEvent->setEvent($this);
        }

        return $this;
    }

    public function removeUserEvent(UserEvent $userEvent): self
    {
        if ($this->userEvents->contains($userEvent)) {
            $this->userEvents->removeElement($userEvent);
            // set the owning side to null (unless already changed)
            if ($userEvent->getEvent() === $this) {
                $userEvent->setEvent(null);
            }
        }

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNbMinParticipant(): ?int
    {
        return $this->nbMinParticipant;
    }

    public function setNbMinParticipant(int $nbMinParticipant): self
    {
        $this->nbMinParticipant = $nbMinParticipant;

        return $this;
    }

    public function getNbMaxParticipant(): ?int
    {
        return $this->nbMaxParticipant;
    }

    public function setNbMaxParticipant(int $nbMaxParticipant): self
    {
        $this->nbMaxParticipant = $nbMaxParticipant;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection|EventPricing[]
     */
    public function getEventPricings(): Collection
    {
        return $this->eventPricings;
    }

    public function addEventPricing(EventPricing $eventPricing): self
    {
        if (!$this->eventPricings->contains($eventPricing)) {
            $this->eventPricings[] = $eventPricing;
            $eventPricing->setEvent($this);
        }

        return $this;
    }

    public function removeEventPricing(EventPricing $eventPricing): self
    {
        if ($this->eventPricings->contains($eventPricing)) {
            $this->eventPricings->removeElement($eventPricing);
            // set the owning side to null (unless already changed)
            if ($eventPricing->getEvent() === $this) {
                $eventPricing->setEvent(null);
            }
        }

        return $this;
    }

    public function getOnlineEvent(): ?bool
    {
        return $this->onlineEvent;
    }

    public function setOnlineEvent(bool $onlineEvent): self
    {
        $this->onlineEvent = $onlineEvent;

        return $this;
    }

}
