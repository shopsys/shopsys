<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

final readonly class ProductPricesResult
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface $basicProductPrice Product price without any discounts (original list price)
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface $sellingProductPrice Product price with discounts applied
     */
    public function __construct(
        public ProductPriceInterface $basicProductPrice,
        public ProductPriceInterface $sellingProductPrice,
    ) {
    }
}
