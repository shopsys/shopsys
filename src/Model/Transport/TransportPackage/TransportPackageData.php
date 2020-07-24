<?php

declare(strict_types=1);

namespace App\Model\Transport\TransportPackage;

class TransportPackageData
{
    /**
     * @var int|string|null
     */
    public $id;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var int|string|null
     */
    public $maxProductPackagesCount;

    /**
     * @var int|string|null
     */
    public $maxWeight;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $priceWithVat;

    /**
     * @var int|string|null
     */
    public $maxGirth;

    /**
     * @var int|string|null
     */
    public $dimension1;

    /**
     * @var int|string|null
     */
    public $dimension2;

    /**
     * @var int|string|null
     */
    public $dimension3;
}
