<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

abstract class AbstractProductRecalculationMessage
{
    /**
     * @param int $productId
     * @param string[] $affectedPropertyNames
     */
    public function __construct(
        public readonly int $productId,
        public readonly array $affectedPropertyNames = []
    ) {
    }
}
