<?php

declare(strict_types=1);

namespace App\Model\Slider;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem as BaseSliderItem;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemData as BaseSliderItemData;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * SliderItem
 *
 * @method void setData(\App\Model\Slider\SliderItemData $sliderItemData)
 * @method void edit(\App\Model\Slider\SliderItemData $sliderItemData)
 */
#[AsMcpTable]
#[ORM\Table(name: 'slider_items')]
#[ORM\Entity]
class SliderItem extends BaseSliderItem
{
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected string $uuid;

    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     */
    public function __construct(BaseSliderItemData $sliderItemData)
    {
        parent::__construct($sliderItemData);

        $this->uuid = $sliderItemData->uuid ?: Uuid::uuid4()->toString();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
