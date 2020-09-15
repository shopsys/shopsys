<?php

declare(strict_types=1);

namespace App\Model\Stock;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class StockData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var bool|null
     */
    public $centralStock;

    /**
     * @var string|null
     */
    public $externalId;

    /**
     * @var string|null
     */
    public $street;

    /**
     * @var string|null
     */
    public $city;

    /**
     * @var string|null
     */
    public $openingHours;

    /**
     * @var string|null
     */
    public $extraordinaryOpeningHours;

    /**
     * @var string|null
     */
    public $contactText1;

    /**
     * @var string|null
     */
    public $contactText2;

    /**
     * @var string|null
     */
    public $contactInfo;

    /**
     * @var string|null
     */
    public $locationLat;

    /**
     * @var string|null
     */
    public $locationLng;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $imageGallery;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public UrlListData $urls;

    public function __construct()
    {
        $this->centralStock = false;
        $this->image = new ImageUploadData();
        $this->imageGallery = new ImageUploadData();
        $this->urls = new UrlListData();
    }
}
