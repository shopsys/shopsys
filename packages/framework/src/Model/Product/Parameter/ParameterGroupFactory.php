<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ParameterGroupFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(ParameterGroupData $data): ParameterGroup
    {
        $entityClassName = $this->entityNameResolver->resolve(ParameterGroup::class);

        return new $entityClassName($data);
    }
}
