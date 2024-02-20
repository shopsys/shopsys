<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInputEnumInterface;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductExportScopeSimple implements ExportScopeInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum[]
     */
    public function getPreconditions(): array
    {
        return [];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Export\Scope\ProductExportScopeInputEnum $exportScopeInput
     * @return \Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum[]
     */
    public function getElasticFieldsByScopeInput(ExportScopeInputEnumInterface $exportScopeInput): array
    {
        return match ($exportScopeInput) {
            ProductExportScopeInputEnum::PRODUCT_UNIT => [
                ProductExportFieldEnum::UNIT
            ],
            ProductExportScopeInputEnum::PRODUCT_BRAND => [
                ProductExportFieldEnum::BRAND,
                ProductExportFieldEnum::BRAND_NAME,
                ProductExportFieldEnum::BRAND_URL,
            ],
            ProductExportScopeInputEnum::PRODUCT_STOCKS => [
                ProductExportFieldEnum::AVAILABILITY,
                ProductExportFieldEnum::AVAILABILITY_DISPATCH_TIME,
                ProductExportFieldEnum::IN_STOCK,
                ProductExportFieldEnum::STOCK_QUANTITY,
            ],
            ProductExportScopeInputEnum::PRODUCT_URL => [
                ProductExportFieldEnum::DETAIL_URL,
                ProductExportFieldEnum::HREFLANG_LINKS,
            ],
            ProductExportScopeInputEnum::PRODUCT_FLAGS => [
                ProductExportFieldEnum::FLAGS,
            ],
            ProductExportScopeInputEnum::PRODUCT_PARAMETERS => [
                ProductExportFieldEnum::PARAMETERS,
            ],
            default => [],
        };
    }
}
