<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

abstract class AbstractProductRecalculationMessage
{
    /**
     * @param int $productId
     * @param string[] $exportScopes
     */
    public function __construct(
        public readonly int $productId,
        public readonly array $exportScopes = [],
    ) {
    }
}
