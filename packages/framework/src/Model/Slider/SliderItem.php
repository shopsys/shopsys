<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * SliderItem
 */
#[AsMcpTable]
#[ORM\Table(name: 'slider_items')]
#[ORM\Entity]
#[EntityImage]
#[EntityImage('web')]
#[EntityImage('mobile')]
class SliderItem implements OrderableEntityInterface, DomainSeparatedEntityInterface
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text')]
    protected $name;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text')]
    protected $link;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[Gedmo\SortableGroup]
    protected $domainId;

    /**
     * @var int|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Gedmo\SortablePosition]
    protected $position;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $hidden;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 7)]
    protected $rgbBackgroundColor;

    /**
     * @var float
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'decimal', precision: 3, scale: 2)]
    protected $opacity;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $datetimeVisibleFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $datetimeVisibleTo;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $routeName;

    public function __construct(SliderItemData $sliderItemData)
    {
        $this->setData($sliderItemData);
    }

    public function edit(SliderItemData $sliderItemData): void
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
    #[Override]
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
    public function setPosition($position): void
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
    public function setDatetimeVisibleFrom($datetimeVisibleFrom): void
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
    public function setDatetimeVisibleTo($datetimeVisibleTo): void
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
