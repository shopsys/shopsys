<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Shopsys\FrameworkBundle\Model\Product\Product;

class GiftCartItemSetup
{
    public function __construct(
        public readonly Product $mainProduct,
        public readonly Product $giftProduct,
        public readonly int $quantity = 0,
    ) {
    }
}
