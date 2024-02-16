<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductVisibilityExportScope extends AbstractProductExportScope
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
     * @return array
     */
    public function getElasticFieldNamesIndexedByEntityFieldNames(): array
    {
        return [
            'Product::name' => [
                ProductExportFieldEnum::NAME,
                ProductExportFieldEnum::DETAIL_URL,
                ProductExportFieldEnum::HREFLANG_LINKS,
            ],
            'Product::hidden' => [ // TODO tohle mění komplet viditelnost produktu - takže buď nebude vůbec v elasticu nebo bude všude true (pro každou pricing group)
                ProductExportFieldEnum::VISIBILITY,
            ],
            'Product::sellingFrom' => [ // TODO tohle mění komplet viditelnost produktu - takže buď nebude vůbec v elasticu nebo bude všude true (pro každou pricing group)
                ProductExportFieldEnum::VISIBILITY,
            ],
            'Product::sellingTo' => [ // TODO tohle mění komplet viditelnost produktu - takže buď nebude vůbec v elasticu nebo bude všude true (pro každou pricing group)
                ProductExportFieldEnum::VISIBILITY,
            ],
            'Product::price' => [
                ProductExportFieldEnum::PRICES,
                ProductExportFieldEnum::VISIBILITY,
            ],
            'Product::categories' => [
                ProductExportFieldEnum::CATEGORIES,
                ProductExportFieldEnum::MAIN_CATEGORY_ID,
            ],
            'Category::name' => [
                ProductExportFieldEnum::VISIBILITY,
            ],
            'Category::lft' => [
                ProductExportFieldEnum::CATEGORIES,
                ProductExportFieldEnum::MAIN_CATEGORY_ID,
            ],
            'Category::rgt' => [
                ProductExportFieldEnum::CATEGORIES,
                ProductExportFieldEnum::MAIN_CATEGORY_ID,
            ],
            'Category::level' => [
                ProductExportFieldEnum::CATEGORIES,
                ProductExportFieldEnum::MAIN_CATEGORY_ID,
            ],
            'Category::parent' => [
                ProductExportFieldEnum::CATEGORIES,
                ProductExportFieldEnum::MAIN_CATEGORY_ID,
            ],
            'Category::enabled' => [
                ProductExportFieldEnum::CATEGORIES,
                ProductExportFieldEnum::MAIN_CATEGORY_ID,
            ],
        ];
    }
}
