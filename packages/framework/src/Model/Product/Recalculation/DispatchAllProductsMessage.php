<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;

class DispatchAllProductsMessage
{
    /**
     * @param string[] $exportScopes
     */
    public function __construct(public readonly array $exportScopes = ProductExportScopeConfig::ALL_SCOPES)
    {
    }
}
