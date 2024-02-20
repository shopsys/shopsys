<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInputEnumInterface;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface;
use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductExportScopeVisibilityAndSellingDenied implements ExportScopeInterface
{
    /**
     * @inheritDoc
     */
    public function getPreconditions(): array
    {
        return [
            ProductExportPreconditionsEnum::VISIBILITY_RECALCULATION,
            ProductExportPreconditionsEnum::SELLING_DENIED_RECALCULATION,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getElasticFieldsByScopeInput(ExportScopeInputEnumInterface $exportScopeInput): array
    {
        return match ($exportScopeInput) {
            ProductExportScopeInputEnum::PRODUCT_VARIANTS => [
                ProductExportFieldEnum::CALCULATED_SELLING_DENIED,
                ProductExportFieldEnum::VISIBILITY,
                ProductExportFieldEnum::VARIANTS,
                ProductExportFieldEnum::IS_VARIANT,
                ProductExportFieldEnum::IS_MAIN_VARIANT,
                ProductExportFieldEnum::MAIN_VARIANT_ID,
            ],
            default => [],
        };
    }
}
