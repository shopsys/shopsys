<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

class ProductInputPriceData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     */
    public $vat;

    /**
     * @var array<int, \Shopsys\FrameworkBundle\Component\Money\Money|null>
     */
    public $manualInputPricesByPricingGroupId = [];
}
