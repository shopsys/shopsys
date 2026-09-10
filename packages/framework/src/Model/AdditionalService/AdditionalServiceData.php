<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

class AdditionalServiceData
{
    /**
     * @var string|null
     */
    public $catnum;

    /**
     * @var array<string, string|null>
     */
    public $name;

    /**
     * @var array<string, string|null>
     */
    public $feedName;

    /**
     * @var array<string, string|null>
     */
    public $zboziDescription;

    /**
     * @var array<string, string|null>
     */
    public $description;

    /**
     * @var array<int, bool>
     */
    public $enabledByDomainId;

    /**
     * @var array<int, bool>
     */
    public $showInFeedsByDomainId;

    /**
     * @var array<int, bool>
     */
    public $useProductVatRateByDomainId;

    /**
     * @var array<int, \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null>
     */
    public $vatsIndexedByDomainId;

    /**
     * @var array<int, \Shopsys\FrameworkBundle\Component\Money\Money|null>
     */
    public $pricesIndexedByDomainId;

    /**
     * @var string|null
     */
    public $zboziServiceType;

    /**
     * @var int
     */
    public $deliveryDaysExtension;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var string|null
     */
    public $uuid;

    public function __construct()
    {
        $this->deliveryDaysExtension = 0;
        $this->name = [];
        $this->feedName = [];
        $this->zboziDescription = [];
        $this->description = [];
        $this->enabledByDomainId = [];
        $this->showInFeedsByDomainId = [];
        $this->useProductVatRateByDomainId = [];
        $this->vatsIndexedByDomainId = [];
        $this->pricesIndexedByDomainId = [];
    }
}
