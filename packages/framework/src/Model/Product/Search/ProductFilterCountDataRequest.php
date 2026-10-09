<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductFilterCountDataRequest
{
    public function __construct(
        protected readonly ProductFilterData $productFilterData,
        protected readonly FilterQuery $baseFilterQuery,
        protected readonly bool $withParameters,
    ) {
    }

    public function getProductFilterData(): ProductFilterData
    {
        return $this->productFilterData;
    }

    public function getBaseFilterQuery(): FilterQuery
    {
        return $this->baseFilterQuery;
    }

    public function isWithParameters(): bool
    {
        return $this->withParameters;
    }
}
