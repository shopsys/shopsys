<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

class PriceListData
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var \DateTimeImmutable|null
     */
    public $validFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    public $validTo;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceData[]
     */
    public $priceListProductPricesData = [];
}
