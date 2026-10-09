<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountDataBatchLoadData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class ProductFilterOptionsBatchLoadData extends ProductFilterCountDataBatchLoadData
{
    public function __construct(
        Category|Brand|Flag|null $entity,
        string $searchText,
        ProductFilterData $productFilterData,
        protected readonly ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ) {
        parent::__construct($entity, $searchText, $productFilterData);
    }

    public function getReadyCategorySeoMix(): ?ReadyCategorySeoMix
    {
        return $this->readyCategorySeoMix;
    }
}
