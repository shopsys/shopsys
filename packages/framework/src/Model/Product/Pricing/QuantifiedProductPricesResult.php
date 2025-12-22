<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;

final readonly class QuantifiedProductPricesResult
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $basicQuantifiedItemPrice Quantified price without any discounts (original list price × quantity)
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $sellingQuantifiedItemPrice Quantified price with discounts applied
     */
    public function __construct(
        public QuantifiedItemPrice $basicQuantifiedItemPrice,
        public QuantifiedItemPrice $sellingQuantifiedItemPrice,
    ) {
    }
}
