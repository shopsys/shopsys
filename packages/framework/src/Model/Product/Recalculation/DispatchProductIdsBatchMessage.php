<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

class DispatchProductIdsBatchMessage
{
    /**
     * @param int[] $productIds
     * @param string[] $exportScopes
     * @param string $productRecalculationPriorityEnum
     */
    public function __construct(
        public readonly array $productIds = [],
        public readonly array $exportScopes = [],
        public readonly string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
    ) {
    }
}
