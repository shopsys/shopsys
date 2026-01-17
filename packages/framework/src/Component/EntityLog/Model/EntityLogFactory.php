<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class EntityLogFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(
        EntityLogData $entityLogData,
    ): EntityLog {
        $entityClassName = $this->entityNameResolver->resolve(EntityLog::class);

        return new $entityClassName($entityLogData);
    }
}
