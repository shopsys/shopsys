<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class ProductFilterBatchLoadDataFactory
{
    public function create(Category|Brand|Flag|null $entity, string $searchText): ProductFilterBatchLoadData
    {
        return new ProductFilterBatchLoadData($entity, $searchText);
    }
}
