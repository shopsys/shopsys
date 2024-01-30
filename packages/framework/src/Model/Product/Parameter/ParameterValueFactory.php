<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ParameterValueFactory implements ParameterValueFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function create(ParameterValueData $data): ParameterValue
    {
        $entityClassName = $this->entityNameResolver->resolve(ParameterValue::class);

        return new $entityClassName($data);
    }
}
