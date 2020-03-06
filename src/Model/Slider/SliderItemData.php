<?php

declare(strict_types=1);

namespace App\Model\Slider;

use Shopsys\FrameworkBundle\Model\Slider\SliderItemData as BaseSliderItemData;

class SliderItemData extends BaseSliderItemData
{
    /**
     * @var \DateTime|null
     */
    public $datetimeVisibleFrom;

    /**
     * @var \DateTime|null
     */
    public $datetimeVisibleTo;

    /**
     * @var string|null
     */
    public $sliderExtendedText;

    /**
     * @var string|null
     */
    public $sliderExtendedTextLink;
}
