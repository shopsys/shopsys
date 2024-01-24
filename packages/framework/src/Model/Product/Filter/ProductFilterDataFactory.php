<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

class ProductFilterDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
    public function create(): ProductFilterData
    {
        return new ProductFilterData();
    }
}
