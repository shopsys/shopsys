<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class ProductFilterOptionsBatchLoadDataFactory
{
    public function create(
        Category|Brand|Flag|null $entity,
        string $searchText,
        ProductFilterData $productFilterData,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ): ProductFilterOptionsBatchLoadData {
        return new ProductFilterOptionsBatchLoadData($entity, $searchText, $productFilterData, $readyCategorySeoMix);
    }
}
