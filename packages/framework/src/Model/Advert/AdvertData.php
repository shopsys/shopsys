<?php

declare(strict_types=1);

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
     * @var string|null
     */
    public $uuid = null;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public array $categories;

    /**
     * @var \DateTime|null
     */
    public $datetimeVisibleFrom;

    /**
     * @var \DateTime|null
     */
    public $datetimeVisibleTo;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $mobileImage;

    public function __construct()
    {
        $this->hidden = false;
        $this->categories = [];
    }
}
