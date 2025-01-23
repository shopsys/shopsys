<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ProductParameterValuesLocalizedDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData
     */
    public function create(): ProductParameterValuesLocalizedData
    {
        return new ProductParameterValuesLocalizedData();
    }
}
