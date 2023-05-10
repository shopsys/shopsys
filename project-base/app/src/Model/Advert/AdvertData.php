<?php

declare(strict_types=1);

namespace App\Model\Advert;

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
}
