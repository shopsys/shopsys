<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;

class DispatchProductIdsBatchMessage
{
    /**
     * @param int[] $productIds
     * @param string[] $exportScopes
     */
    public function __construct(
        public readonly array $productIds = [],
        public readonly array $exportScopes = ProductExportScopeConfig::ALL_SCOPES,
        public readonly string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
    ) {
    }
}
