<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

class AdvertData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var string|null
     */
    public $code;

    /**
     * @var string|null
     */
    public $link;

    /**
     * @var string|null
     */
    public $positionName;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var string
     */
    public $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public array $categories;

    public function __construct()
    {
        $this->hidden = false;
        $this->categories = [];
    }
}
