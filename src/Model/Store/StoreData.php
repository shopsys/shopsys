<?php

declare(strict_types=1);

namespace App\Model\Store;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;

class StoreData
{
    public array $isEnabledOnDomains;

    public bool $isDefault = false;

    public string $name;

    public ?string $description = null;

    public ?string $externalId = null;

    public ?string $address = null;

    public ?string $openingHours = null;

    public ?string $contactInfo = null;

    public ?string $specialMessage = null;

    public ?string $locationLatitude = null;

    public ?string $locationLongitude = null;

    public ImageUploadData $image;

    public function __construct()
    {
        $this->image = new ImageUploadData();
    }
}
