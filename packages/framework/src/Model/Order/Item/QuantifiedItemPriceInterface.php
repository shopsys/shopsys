<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

interface QuantifiedItemPriceInterface
{
    public function getUnitPrice(): PriceInterface;

    public function getTotalPrice(): PriceInterface;

    public function getVat(): Vat;
}
