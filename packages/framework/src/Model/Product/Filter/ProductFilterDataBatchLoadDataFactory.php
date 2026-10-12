<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class ProductFilterDataBatchLoadDataFactory
{
    public function create(
        Category|Brand|Flag|null $entity,
        string $searchText,
        ProductFilterData $productFilterData,
    ): ProductFilterDataBatchLoadData {
        return new ProductFilterDataBatchLoadData($entity, $searchText, $productFilterData);
    }
}
