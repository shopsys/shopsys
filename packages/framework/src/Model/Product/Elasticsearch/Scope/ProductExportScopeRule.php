<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope;

class ProductExportScopeRule
{
    /**
     * @param string[] $productExportFields
     * @param string[] $productExportPreconditions
     */
    public function __construct(
        public readonly array $productExportFields,
        public readonly array $productExportPreconditions = [],
    ) {
    }
}
