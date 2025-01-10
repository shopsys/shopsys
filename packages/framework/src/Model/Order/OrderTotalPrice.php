<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

final class OrderTotalPrice
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $productPriceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $productPriceWithoutVat
     */
    public function __construct(
        private readonly Money $priceWithVat,
        private readonly Money $priceWithoutVat,
        private readonly Money $productPriceWithVat,
        private readonly Money $productPriceWithoutVat,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getPrice(): Price
    {
        return new Price($this->priceWithoutVat, $this->priceWithVat);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getProductPrice(): Price
    {
        return new Price($this->productPriceWithoutVat, $this->productPriceWithVat);
    }
}
