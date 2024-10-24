<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ParameterGroupFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup
     */
    public function create(ParameterGroupData $data): ParameterGroup
    {
        $entityClassName = $this->entityNameResolver->resolve(ParameterGroup::class);

        return new $entityClassName($data);
    }
}
