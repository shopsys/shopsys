<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class ProductFilterCountDataBatchLoadData extends ProductFilterBatchLoadData
{
    public function __construct(
        Category|Brand|Flag|null $entity,
        string $searchText,
        protected readonly ProductFilterData $productFilterData,
    ) {
        parent::__construct($entity, $searchText);
    }

    public function getProductFilterData(): ProductFilterData
    {
        return $this->productFilterData;
    }
}
