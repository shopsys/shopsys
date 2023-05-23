<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Money\Money;

class OrderTotalPrice
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $productPriceWithVat
     */
    public function __construct(
        protected readonly Money $priceWithVat,
        protected readonly Money $priceWithoutVat,
        protected readonly Money $productPriceWithVat,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceWithVat(): Money
    {
        return $this->priceWithVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceWithoutVat(): Money
    {
        return $this->priceWithoutVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getProductPriceWithVat(): Money
    {
        return $this->productPriceWithVat;
    }
}
