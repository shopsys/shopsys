<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class ProductFilterBatchLoadData
{
    public function __construct(
        protected readonly Category|Brand|Flag|null $entity,
        protected readonly string $searchText,
    ) {
    }

    public function getEntity(): Category|Brand|Flag|null
    {
        return $this->entity;
    }

    public function getSearchText(): string
    {
        return $this->searchText;
    }
}
