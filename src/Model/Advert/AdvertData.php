<?php

declare(strict_types=1);

namespace App\Model\Advert;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Model\Advert\AdvertData as BaseAdvertData;

class AdvertData extends BaseAdvertData
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
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $mobileImage;

    /**
     * @var \App\Model\Category\Category[]
     */
    public $categories;

    public function __construct()
    {
        parent::__construct();
        $this->mobileImage = new ImageUploadData();
        $this->categories = [];
    }
}
