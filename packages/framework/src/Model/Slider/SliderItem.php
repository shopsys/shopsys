<?php

namespace Shopsys\FrameworkBundle\Model\Slider;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

/**
 * SliderItem
 *
 * @ORM\Table(name="slider_items")
 * @ORM\Entity
 */
class SliderItem implements OrderableEntityInterface
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $link;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $position;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    public function __construct(SliderItemData $sliderItemData)
    {
        $this->domainId = $sliderItemData->domainId;
        $this->name = $sliderItemData->name;
        $this->link = $sliderItemData->link;
        $this->hidden = $sliderItemData->hidden;
    }

    public function edit(SliderItemData $sliderItemData): void
    {
        $this->name = $sliderItemData->name;
        $this->link = $sliderItemData->link;
        $this->hidden = $sliderItemData->hidden;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }
    
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
