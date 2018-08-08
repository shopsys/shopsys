<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Product;

interface ProductParameterValueFactoryInterface
{

    public function create(
        Product $product,
        Parameter $parameter,
        ParameterValue $value
    ): ProductParameterValue;
}
