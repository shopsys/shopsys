<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ParameterFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(ParameterData $data): Parameter
    {
        $entityClassName = $this->entityNameResolver->resolve(Parameter::class);

        return new $entityClassName($data);
    }
}
