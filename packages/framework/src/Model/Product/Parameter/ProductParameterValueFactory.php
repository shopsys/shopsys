<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductParameterValueFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        Product $product,
        Parameter $parameter,
        ParameterValue $value,
    ): ProductParameterValue {
        $entityClassName = $this->entityNameResolver->resolve(ProductParameterValue::class);

        return new $entityClassName($product, $parameter, $value);
    }
}
