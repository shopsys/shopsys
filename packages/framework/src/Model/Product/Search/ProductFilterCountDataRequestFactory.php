<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductFilterCountDataRequestFactory
{
    public function create(
        ProductFilterData $productFilterData,
        FilterQuery $baseFilterQuery,
        bool $withParameters,
    ): ProductFilterCountDataRequest {
        return new ProductFilterCountDataRequest($productFilterData, $baseFilterQuery, $withParameters);
    }
}
