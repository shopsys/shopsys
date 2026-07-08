<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

class PriceWithLimitData
{
    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\Money\Money|null>
     */
    public $pricesByCurrencyCode = [];

    /**
     * @var int|null
     */
    public $maxWeight;

    /**
     * @var array<string, int|null>
     */
    public $transportPriceIdsByCurrencyCode = [];
}
