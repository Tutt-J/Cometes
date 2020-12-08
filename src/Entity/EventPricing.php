<?php

namespace App\Entity;

use App\Repository\EventPricingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventPricingRepository::class)
 */
class EventPricing
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class, inversedBy="eventPricings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $event;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $content;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endValidityDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startValidityDate;

    public function __toString()
    {
       return $this->content.' - '.$this->price.'€';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }


    public function getStartValidityDate(): ?\DateTimeInterface
    {
        return $this->startValidityDate;
    }

    public function setStartValidityDate(?\DateTimeInterface $startValidityDate): self
    {
        $this->startValidityDate = $startValidityDate;

        return $this;
    }

    public function getEndValidityDate(): ?\DateTimeInterface
    {
        return $this->endValidityDate;
    }

    public function setEndValidityDate(?\DateTimeInterface $endValidityDate): self
    {
        $this->endValidityDate = $endValidityDate;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }
}
