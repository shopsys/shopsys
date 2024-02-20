<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInputEnumInterface;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface;
use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductExportScopeVisibility implements ExportScopeInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum[]
     */
    public function getPreconditions(): array
    {
        return [
            ProductExportPreconditionsEnum::VISIBILITY_RECALCULATION,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getElasticFieldsByScopeInput(ExportScopeInputEnumInterface $exportScopeInput): array
    {
        // TODO nestálo by za to zabalit do jednoho inputu takové fieldy, které tady mám vícekrát? za mě teď spíše ne, protože nechci ztratit tu konkrétní informaci o tom, že sellingFrom/To
        return match ($exportScopeInput) {
            ProductExportScopeInputEnum::PRODUCT_NAME => [
                ProductExportFieldEnum::NAME,
                ProductExportFieldEnum::DETAIL_URL,
                ProductExportFieldEnum::HREFLANG_LINKS,
            ],
            ProductExportScopeInputEnum::PRODUCT_HIDDEN,
            ProductExportScopeInputEnum::PRODUCT_SELLING_FROM,
            ProductExportScopeInputEnum::PRODUCT_SELLING_TO,
            ProductExportScopeInputEnum::CATEGORY_NAME => [
                ProductExportFieldEnum::VISIBILITY,
            ],
            ProductExportScopeInputEnum::PRODUCT_PRICE => [
                ProductExportFieldEnum::PRICES,
                ProductExportFieldEnum::VISIBILITY,
            ],
            ProductExportScopeInputEnum::PRODUCT_CATEGORIES,
            ProductExportScopeInputEnum::CATEGORY_TREE,
            ProductExportScopeInputEnum::CATEGORY_ENABLED => [
                ProductExportFieldEnum::CATEGORIES,
                ProductExportFieldEnum::MAIN_CATEGORY_ID,
            ],
            default => [],
        };
    }
}
