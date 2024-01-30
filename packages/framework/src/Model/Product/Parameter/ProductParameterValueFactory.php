<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductParameterValueFactory implements ProductParameterValueFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $value
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue
     */
    public function create(
        Product $product,
        Parameter $parameter,
        ParameterValue $value,
    ): ProductParameterValue {
        $entityClassName = $this->entityNameResolver->resolve(ProductParameterValue::class);

        return new $entityClassName($product, $parameter, $value);
    }
}
