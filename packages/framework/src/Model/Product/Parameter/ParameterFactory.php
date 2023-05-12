<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ParameterFactory implements ParameterFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function create(ParameterData $data): Parameter
    {
        $classData = $this->entityNameResolver->resolve(Parameter::class);

        return new $classData($data);
    }
}
