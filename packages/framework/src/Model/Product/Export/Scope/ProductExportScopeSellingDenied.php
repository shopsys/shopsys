<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInputEnumInterface;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface;
use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductExportScopeSellingDenied implements ExportScopeInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum[]
     */
    public function getPreconditions(): array
    {
        return [
            ProductExportPreconditionsEnum::SELLING_DENIED_RECALCULATION,
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Export\Scope\ProductExportScopeInputEnum $exportScopeInput
     * @return \Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum[]
     */
    public function getElasticFieldsByScopeInput(ExportScopeInputEnumInterface $exportScopeInput): array
    {
        return match ($exportScopeInput) {
            ProductExportScopeInputEnum::PRODUCT_SELLING_DENIED => [
                ProductExportFieldEnum::SELLING_DENIED,
                ProductExportFieldEnum::CALCULATED_SELLING_DENIED,
            ],
            default => [],
        };
    }
}
