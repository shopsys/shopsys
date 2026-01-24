<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Override;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

final class QuantifiedItemPrice implements QuantifiedItemPriceInterface
{
    public function __construct(
        private readonly PriceInterface $unitPrice,
        private readonly PriceInterface $totalPrice,
        private readonly Vat $vat,
    ) {
    }

    #[Override]
    public function getUnitPrice(): PriceInterface
    {
        return $this->unitPrice;
    }

    #[Override]
    public function getTotalPrice(): PriceInterface
    {
        return $this->totalPrice;
    }

    #[Override]
    public function getVat(): Vat
    {
        return $this->vat;
    }
}
