<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductParameterValueFactory implements ProductParameterValueFactoryInterface
{

    public function create(
        Product $product,
        Parameter $parameter,
        ParameterValue $value
    ): ProductParameterValue {
        return new ProductParameterValue($product, $parameter, $value);
    }
}
