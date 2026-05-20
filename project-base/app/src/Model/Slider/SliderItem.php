<?php

declare(strict_types=1);

namespace App\Model\Slider;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem as BaseSliderItem;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemData as BaseSliderItemData;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * SliderItem
 *
 * @method void setData(\App\Model\Slider\SliderItemData $sliderItemData)
 */
#[AsMcpTable]
#[ORM\Table(name: 'slider_items')]
#[ORM\Entity]
class SliderItem extends BaseSliderItem
{
    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: false)]
    protected $gtmId;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $gtmCreative;

    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected string $uuid;

    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     */
    public function __construct(BaseSliderItemData $sliderItemData)
    {
        parent::__construct($sliderItemData);

        $this->gtmId = $sliderItemData->gtmId;
        $this->gtmCreative = $sliderItemData->gtmCreative;
        $this->uuid = $sliderItemData->uuid ?: Uuid::uuid4()->toString();
    }

    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     */
    #[Override]
    public function edit(BaseSliderItemData $sliderItemData): void
    {
        parent::edit($sliderItemData);

        $this->gtmId = $sliderItemData->gtmId;
        $this->gtmCreative = $sliderItemData->gtmCreative;
    }

    public function getGtmId(): string
    {
        return $this->gtmId;
    }

    public function getGtmCreative(): ?string
    {
        return $this->gtmCreative;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
