<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

class SliderItemData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $link;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string
     */
    public $rgbBackgroundColor;

    /**
     * @var float
     */
    public $opacity;

    /**
     * @var \DateTimeImmutable|null
     */
    public $datetimeVisibleFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    public $datetimeVisibleTo;

    public function __construct()
    {
        $this->hidden = false;
        $this->rgbBackgroundColor = '#808080';
        $this->opacity = 0.8;
    }
}
