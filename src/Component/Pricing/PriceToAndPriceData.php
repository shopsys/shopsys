<?php

declare(strict_types=1);

namespace App\Component\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;

class PriceToAndPriceData
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public ?Money $priceTo = null;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public ?Money $price = null;
}
