<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ParameterValueFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(ParameterValueData $data): ParameterValue
    {
        $entityClassName = $this->entityNameResolver->resolve(ParameterValue::class);

        return new $entityClassName($data);
    }
}
