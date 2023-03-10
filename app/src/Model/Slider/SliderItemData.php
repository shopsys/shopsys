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

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $mobileImage;

    /**
     * @var string
     */
    public $gtmId;

    /**
     * @var string|null
     */
    public $gtmCreative;

    /**
     * @var string|null
     */
    public ?string $uuid = null;
}
