<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;

/**
 * @property \App\Model\Payment\Payment[] $payments
 */
class TransportData extends BaseTransportData
{
    /**
     * @var \App\Model\Product\Type\ProductType[]
     */
    public $productTypes;

    /**
     * @var bool
     */
    public $personalPickup;

    /**
     * @var string
     */
    public string $type;

    /**
     * @var \App\Model\Transport\TransportPackage\TransportPackageData[]
     */
    public array $transportPackages;

    /**
     * @var bool
     */
    public $isOverLimitTransport;

    public function __construct()
    {
        parent::__construct();
        $this->productTypes = [];
        $this->personalPickup = false;
        $this->transportPackages = [];
        $this->isOverLimitTransport = false;
    }
}
