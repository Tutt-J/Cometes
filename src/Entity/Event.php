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
     * @ORM\OneToMany(targetEntity="UserEvent", mappedBy="event", fetch="EXTRA_LAZY")
     */
    private $userEvents;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
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

    /**
     * @ORM\Column(type="boolean")
     */
    private $isOnline;

    /**
     * @ORM\Column(type="boolean")
     */
    private $allowFriend;

    /**
     * @ORM\Column(type="boolean")
     */
    private $allowAlready;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isCollaboration;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $collaborationLink;

    /**
     * @ORM\ManyToMany(targetEntity=Type::class, inversedBy="events")
     */
    private $Type;



    public function __construct()
    {
        $this->userEvents = new ArrayCollection();
        $this->eventPricings = new ArrayCollection();
        $this->Type = new ArrayCollection();
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

    public function getIsOnline(): ?bool
    {
        return $this->isOnline;
    }

    public function setIsOnline(bool $isOnline): self
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getAllowFriend(): ?bool
    {
        return $this->allowFriend;
    }

    public function setAllowFriend(bool $allowFriend): self
    {
        $this->allowFriend = $allowFriend;

        return $this;
    }

    public function getAllowAlready(): ?bool
    {
        return $this->allowAlready;
    }

    public function setAllowAlready(bool $allowAlready): self
    {
        $this->allowAlready = $allowAlready;

        return $this;
    }

    public function getIsCollaboration(): ?bool
    {
        return $this->isCollaboration;
    }

    public function setIsCollaboration(bool $isCollaboration): self
    {
        $this->isCollaboration = $isCollaboration;

        return $this;
    }

    public function getCollaborationLink(): ?string
    {
        return $this->collaborationLink;
    }

    public function setCollaborationLink(?string $collaborationLink): self
    {
        $this->collaborationLink = $collaborationLink;

        return $this;
    }

    /**
     * @return Collection|Type[]
     */
    public function getType(): Collection
    {
        return $this->Type;
    }

    public function addType(Type $type): self
    {
        if (!$this->Type->contains($type)) {
            $this->Type[] = $type;
        }

        return $this;
    }

    public function removeType(Type $type): self
    {
        $this->Type->removeElement($type);

        return $this;
    }

}
