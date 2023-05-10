<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Model\Stock\Stock;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Model\Country\Country;

class StoreData
{
    public array $isEnabledOnDomains;

    public bool $isDefault = false;

    public string $name;

    public ?Stock $stock = null;

    public ?string $description = null;

    public ?string $externalId = null;

    public ?string $street = null;

    public ?string $city = null;

    public ?string $postcode = null;

    public ?Country $country = null;

    public ?string $openingHours = null;

    public ?string $contactInfo = null;

    public ?string $specialMessage = null;

    public ?string $locationLatitude = null;

    public ?string $locationLongitude = null;

    public ImageUploadData $image;

    public ?string $uuid = null;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public UrlListData $urls;

    public function __construct()
    {
        $this->urls = new UrlListData();
    }
}
