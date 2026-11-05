<?php

declare(strict_types=1);

namespace App\Model\Slider;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem as BaseSliderItem;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * SliderItem
 *
 * @method void setData(\App\Model\Slider\SliderItemData $sliderItemData)
 * @method void edit(\App\Model\Slider\SliderItemData $sliderItemData)
 * @method __construct(\App\Model\Slider\SliderItemData $sliderItemData)
 */
#[AsMcpTable]
#[ORM\Table(name: 'slider_items')]
#[ORM\Entity]
class SliderItem extends BaseSliderItem
{
}
