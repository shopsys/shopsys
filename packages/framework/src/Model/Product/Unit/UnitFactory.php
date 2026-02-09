<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class UnitFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(UnitData $data): Unit
    {
        $entityClassName = $this->entityNameResolver->resolve(Unit::class);

        return new $entityClassName($data);
    }
}
