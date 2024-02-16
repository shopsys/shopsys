<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductAvailabilityExportScope extends AbstractProductExportScope
{
    protected const array AVAILABILITY_ELASTIC_FIELDS = [
        ProductExportFieldEnum::AVAILABILITY,
        ProductExportFieldEnum::AVAILABILITY_DISPATCH_TIME,
        ProductExportFieldEnum::IN_STOCK,
        ProductExportFieldEnum::STOCK_QUANTITY,
    ];

    /**
     * @return array
     */
    public function getElasticFieldNamesIndexedByEntityFieldNames(): array
    {
        return [
            'Product::stocks' => self::AVAILABILITY_ELASTIC_FIELDS,
            'ProductStock::productQuantity' => self::AVAILABILITY_ELASTIC_FIELDS,
            'StockDomain::enabled' => self::AVAILABILITY_ELASTIC_FIELDS,
        ];
    }
}
