<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

interface OrderTotalPriceInterface
{
    public function getPrice(): PriceInterface;

    public function getProductPrice(): PriceInterface;
}
