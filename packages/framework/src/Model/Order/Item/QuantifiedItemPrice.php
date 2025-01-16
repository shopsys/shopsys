<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

final class QuantifiedItemPrice
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $unitPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $totalPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     */
    public function __construct(
        private readonly PriceInterface $unitPrice,
        private readonly PriceInterface $totalPrice,
        private readonly Vat $vat,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getUnitPrice(): PriceInterface
    {
        return $this->unitPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getTotalPrice(): PriceInterface
    {
        return $this->totalPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getVat(): Vat
    {
        return $this->vat;
    }
}
