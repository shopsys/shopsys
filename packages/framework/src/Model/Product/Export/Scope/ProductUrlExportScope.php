<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductUrlExportScope extends AbstractProductExportScope
{
    public function getElasticFieldNamesIndexedByEntityFieldNames(): array
    {
        return [
            'Product::url' => [
                ProductExportFieldEnum::DETAIL_URL,
                ProductExportFieldEnum::HREFLANG_LINKS,
            ],
        ];
    }
}
