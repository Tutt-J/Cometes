<?php

namespace App\Entity;

use App\Service\ContentsTrait;
use Cocur\Slugify\Slugify;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContentRepository")
 */
class Content
{
    use TimestampableEntity;
    use ContentsTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $fidelityPrice;

    /**
     * @ORM\Column(type="date")
     */
    private $eventDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ref;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Image")
     */
    private $img;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isOnline;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPack;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Content")
     */
    private $pack;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;


    /**
     * @ORM\OneToMany(targetEntity="PurchaseContent", mappedBy="content")
     */
    private $purchaseContent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $onlineLink;

    /**
     * @ORM\ManyToOne(targetEntity="Type", inversedBy="contents", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $neverPassed;


    public function __construct()
    {
        $this->purchaseContent = new ArrayCollection();
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

    public function getFidelityPrice(): ?int
    {
        return $this->fidelityPrice;
    }

    public function setFidelityPrice(int $fidelityPrice): self
    {
        $this->fidelityPrice = $fidelityPrice;

        return $this;
    }

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->eventDate;
    }

    public function setEventDate(\DateTimeInterface $eventDate): self
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

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

    public function getIsPack(): ?bool
    {
        return $this->isPack;
    }

    public function setIsPack(bool $isPack): self
    {
        $this->isPack = $isPack;

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

    public function getOnlineLink(): ?string
    {
        return $this->onlineLink;
    }

    public function setOnlineLink(?string $onlineLink): self
    {
        $this->onlineLink = $onlineLink;

        return $this;
    }

    public function getNeverPassed(): ?bool
    {
        return $this->neverPassed;
    }

    public function setNeverPassed(bool $neverPassed): self
    {
        $this->neverPassed = $neverPassed;

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

    public function getPack(): ?self
    {
        return $this->pack;
    }

    public function setPack(?self $pack): self
    {
        $this->pack = $pack;

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

    /**
     * @return Collection|PurchaseContent[]
     */
    public function getPurchaseContent(): Collection
    {
        return $this->purchaseContent;
    }

    public function addPurchaseContent(PurchaseContent $purchaseContent): self
    {
        if (!$this->purchaseContent->contains($purchaseContent)) {
            $this->purchaseContent[] = $purchaseContent;
            $purchaseContent->setContent($this);
        }

        return $this;
    }

    public function removePurchaseContent(PurchaseContent $purchaseContent): self
    {
        if ($this->purchaseContent->contains($purchaseContent)) {
            $this->purchaseContent->removeElement($purchaseContent);
            // set the owning side to null (unless already changed)
            if ($purchaseContent->getContent() === $this) {
                $purchaseContent->setContent(null);
            }
        }

        return $this;
    }


}
