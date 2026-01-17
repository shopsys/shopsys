<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ReadyCategorySeoMixParameterParameterValueFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(
        Parameter $parameter,
        ParameterValue $parameterValue,
    ): ReadyCategorySeoMixParameterParameterValue {
        $entityClassName = $this->entityNameResolver->resolve(ReadyCategorySeoMixParameterParameterValue::class);

        return new $entityClassName($parameter, $parameterValue);
    }
}
