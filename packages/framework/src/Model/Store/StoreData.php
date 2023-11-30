<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class StoreData
{
    /**
     * @var array<int, bool>
     */
    public $isEnabledOnDomains;

    /**
     * @var bool
     */
    public $isDefault = false;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Stock\Stock|null
     */
    public $stock;

    /**
     * @var string|null
     */
    public $description;

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
    public $postcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public $country;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[]
     */
    public $openingHours = [];

    /**
     * @var string|null
     */
    public $contactInfo;

    /**
     * @var string|null
     */
    public $specialMessage;

    /**
     * @var string|null
     */
    public $locationLatitude;

    /**
     * @var string|null
     */
    public $locationLongitude;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData|null
     */
    public $image;

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public $urls;

    public function __construct()
    {
        $this->urls = new UrlListData();
    }
}
