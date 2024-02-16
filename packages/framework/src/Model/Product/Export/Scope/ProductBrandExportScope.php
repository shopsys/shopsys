<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductBrandExportScope extends AbstractProductExportScope
{
    public function getElasticFieldNamesIndexedByEntityFieldNames(): array
    {
        return [
            'Product::brand' => [
                ProductExportFieldEnum::BRAND,
                ProductExportFieldEnum::BRAND_NAME,
                ProductExportFieldEnum::BRAND_URL,
            ],
            'Brand::name' => [
                ProductExportFieldEnum::BRAND_NAME,
                ProductExportFieldEnum::BRAND_URL,
            ],
            'Brand::url' => [
                ProductExportFieldEnum::BRAND_URL,
            ],
        ];
    }
}
