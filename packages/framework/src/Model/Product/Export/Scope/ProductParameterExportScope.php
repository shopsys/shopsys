<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductParameterExportScope extends AbstractProductExportScope
{
    public function getElasticFieldNamesIndexedByEntityFieldNames(): array
    {
        return [
            'Product::parameters' => [
                ProductExportFieldEnum::PARAMETERS,
            ],
        ];
    }
}
