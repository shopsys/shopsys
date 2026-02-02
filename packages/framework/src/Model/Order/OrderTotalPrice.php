<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

final class OrderTotalPrice implements OrderTotalPriceInterface
{
    public function __construct(
        private readonly Money $priceWithVat,
        private readonly Money $priceWithoutVat,
        private readonly Money $productPriceWithVat,
        private readonly Money $productPriceWithoutVat,
    ) {
    }

    #[Override]
    public function getPrice(): PriceInterface
    {
        return new Price($this->priceWithoutVat, $this->priceWithVat);
    }

    #[Override]
    public function getProductPrice(): PriceInterface
    {
        return new Price($this->productPriceWithoutVat, $this->productPriceWithVat);
    }
}
