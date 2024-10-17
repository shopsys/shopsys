<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ReadyCategorySeoMixParameterParameterValueFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixParameterParameterValue
     */
    public function create(
        Parameter $parameter,
        ParameterValue $parameterValue,
    ): ReadyCategorySeoMixParameterParameterValue {
        $entityClassName = $this->entityNameResolver->resolve(ReadyCategorySeoMixParameterParameterValue::class);

        return new $entityClassName($parameter, $parameterValue);
    }
}
