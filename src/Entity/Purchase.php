<?php

namespace App\Entity;

use App\Service\ArticlesTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PurchaseRepository")
 */
class Purchase
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
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="PurchaseContent", mappedBy="purchase", fetch="EXTRA_LAZY")
     */
    private $purchaseContent;

    /**
     * @ORM\OneToOne(targetEntity="UserEvent", mappedBy="purchase")
     */
    private $userEvent;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $stripeId;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $content;

    public function __construct()
    {
        $this->purchaseContent = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(string $stripeId): self
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

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
            $purchaseContent->setPurchase($this);
        }

        return $this;
    }

    public function removePurchaseContent(PurchaseContent $purchaseContent): self
    {
        if ($this->purchaseContent->contains($purchaseContent)) {
            $this->purchaseContent->removeElement($purchaseContent);
            // set the owning side to null (unless already changed)
            if ($purchaseContent->getPurchase() === $this) {
                $purchaseContent->setPurchase(null);
            }
        }

        return $this;
    }

    public function getUserEvent(): ?UserEvent
    {
        return $this->userEvent;
    }

    public function setUserEvent(?UserEvent $userEvent): self
    {
        $this->userEvent = $userEvent;

        // set (or unset) the owning side of the relation if necessary
        $newPurchase = null === $userEvent ? null : $this;
        if ($userEvent->getPurchase() !== $newPurchase) {
            $userEvent->setPurchase($newPurchase);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }


}
