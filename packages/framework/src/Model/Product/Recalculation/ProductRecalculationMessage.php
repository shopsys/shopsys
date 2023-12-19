<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

class ProductRecalculationMessage
{
    /**
     * @param int $productId
     */
    public function __construct(
        public readonly int $productId,
    ) {
    }
}
