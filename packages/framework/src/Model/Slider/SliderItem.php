<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

/**
 * SliderItem
 */
#[ORM\Table(name: 'slider_items')]
#[ORM\Entity]
class SliderItem implements OrderableEntityInterface
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text')]
    protected $name;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text')]
    protected $link;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[Gedmo\SortableGroup]
    protected $domainId;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Gedmo\SortablePosition]
    protected $position;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $hidden;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 7)]
    protected $rgbBackgroundColor;

    /**
     * @var float
     */
    #[ORM\Column(type: 'decimal', precision: 3, scale: 2)]
    protected $opacity;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $datetimeVisibleFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $datetimeVisibleTo;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $routeName;

    public function __construct(SliderItemData $sliderItemData)
    {
        $this->setData($sliderItemData);
    }

    public function edit(SliderItemData $sliderItemData)
    {
        $this->setData($sliderItemData);
    }

    protected function setData(SliderItemData $sliderItemData): void
    {
        $this->domainId = $sliderItemData->domainId;
        $this->name = $sliderItemData->name;
        $this->link = $sliderItemData->link;
        $this->hidden = $sliderItemData->hidden;
        $this->description = $sliderItemData->description;
        $this->rgbBackgroundColor = $sliderItemData->rgbBackgroundColor;
        $this->opacity = $sliderItemData->opacity;
        $this->datetimeVisibleFrom = $sliderItemData->datetimeVisibleFrom;
        $this->datetimeVisibleTo = $sliderItemData->datetimeVisibleTo;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    #[Override]
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getRgbBackgroundColor()
    {
        return $this->rgbBackgroundColor;
    }

    /**
     * @return float
     */
    public function getOpacity()
    {
        return $this->opacity;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDatetimeVisibleFrom()
    {
        return $this->datetimeVisibleFrom;
    }

    /**
     * @param \DateTimeImmutable|null $datetimeVisibleFrom
     */
    public function setDatetimeVisibleFrom($datetimeVisibleFrom)
    {
        $this->datetimeVisibleFrom = $datetimeVisibleFrom;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDatetimeVisibleTo()
    {
        return $this->datetimeVisibleTo;
    }

    /**
     * @param \DateTimeImmutable|null $datetimeVisibleTo
     */
    public function setDatetimeVisibleTo($datetimeVisibleTo)
    {
        $this->datetimeVisibleTo = $datetimeVisibleTo;
    }

    /**
     * @return string|null
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param string|null $routeName
     */
    public function setRouteName($routeName): void
    {
        $this->routeName = $routeName;
    }
}
