<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductSellingDeniedExportScope extends AbstractProductExportScope
{
    public function getPreconditions(): array
    {
        return [
            ProductExportPreconditionsEnum::SELLING_DENIED_RECALCULATION,
        ];
    }

    public function getElasticFieldNamesIndexedByEntityFieldNames(): array
    {
        return [
            'Product::sellingDenied' => [
                ProductExportFieldEnum::SELLING_DENIED,
                ProductExportFieldEnum::CALCULATED_SELLING_DENIED,
            ],
        ];
    }
}
