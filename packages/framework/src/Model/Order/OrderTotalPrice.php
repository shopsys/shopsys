<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class OrderTotalPrice
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $productPriceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $productPriceWithoutVat
     */
    public function __construct(
        protected readonly Money $priceWithVat,
        protected readonly Money $priceWithoutVat,
        protected readonly Money $productPriceWithVat,
        protected readonly Money $productPriceWithoutVat,
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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getPrice(): Price
    {
        return new Price($this->priceWithoutVat, $this->priceWithVat);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getProductPriceWithVat(): Money
    {
        return $this->productPriceWithVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getProductPriceWithoutVat(): Money
    {
        return $this->productPriceWithoutVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getProductPrice(): Price
    {
        return new Price($this->productPriceWithoutVat, $this->productPriceWithVat);
    }
}
