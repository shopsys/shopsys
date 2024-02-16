<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductVariantsExportScope extends AbstractProductExportScope
{
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
    public function getElasticFieldNamesIndexedByEntityFieldNames(): array
    {
        return [
            'Product::variants' => [
                ProductExportFieldEnum::CALCULATED_SELLING_DENIED,
                ProductExportFieldEnum::VISIBILITY,
                ProductExportFieldEnum::VARIANTS,
            ],
            'Product::mainVariant' => [
                ProductExportFieldEnum::CALCULATED_SELLING_DENIED,
                ProductExportFieldEnum::VISIBILITY,
                ProductExportFieldEnum::VARIANTS,
                ProductExportFieldEnum::IS_VARIANT,
                ProductExportFieldEnum::IS_MAIN_VARIANT,
                ProductExportFieldEnum::MAIN_VARIANT_ID,
            ],
            'Product::variantType' => [
                ProductExportFieldEnum::CALCULATED_SELLING_DENIED,
                ProductExportFieldEnum::VISIBILITY,
                ProductExportFieldEnum::VARIANTS,
                ProductExportFieldEnum::IS_VARIANT,
                ProductExportFieldEnum::IS_MAIN_VARIANT,
                ProductExportFieldEnum::MAIN_VARIANT_ID,
            ],
        ];
    }
}
