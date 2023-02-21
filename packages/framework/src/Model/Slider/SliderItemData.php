<?php

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

    public function __construct()
    {
        $this->hidden = false;
    }
}
