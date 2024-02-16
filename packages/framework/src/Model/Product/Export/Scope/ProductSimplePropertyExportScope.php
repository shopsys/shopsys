<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

// TODO na projektu nikoho nenutím ten scope vytvářet, když jej nepotřebuje, tam mu stačí přidat záznam do ProductExportScopeEnum + ProductExportRepository
class ProductSimplePropertyExportScope extends AbstractProductExportScope
{
    public function getElasticFieldNamesIndexedByEntityFieldNames(): array
    {
        return [
            'Product::partno' => [
                ProductExportFieldEnum::PARTNO,
            ],
            'Product::catnum' => [
                ProductExportFieldEnum::CATNUM,
            ],
            'Product::ean' => [
                ProductExportFieldEnum::EAN,
            ],
            'Product::id' => [
                ProductExportFieldEnum::ID,
            ],
            'Product::uuid' => [
                ProductExportFieldEnum::UUID,
            ],
            'Product::description' => [
                ProductExportFieldEnum::DESCRIPTION,
            ],
            'Product::shortDescription' => [
                ProductExportFieldEnum::SHORT_DESCRIPTION,
            ],
            'Product::orderingPriority' => [
                ProductExportFieldEnum::ORDERING_PRIORITY,
            ],
            'Product::unit' => [
                ProductExportFieldEnum::UNIT,
            ],
            'Product::seoH1' => [
                ProductExportFieldEnum::SEO_H1,
            ],
            'Product::seoMetaDescription' => [
                ProductExportFieldEnum::SEO_META_DESCRIPTION,
            ],
            'Product::seoTitle' => [
                ProductExportFieldEnum::SEO_TITLE,
            ],
            'Product::hreflangLinks' => [
                ProductExportFieldEnum::HREFLANG_LINKS,
            ],
        ];
    }
}
