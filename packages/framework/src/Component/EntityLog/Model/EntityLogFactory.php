<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class EntityLogFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogData $entityLogData
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog
     */
    public function create(
        EntityLogData $entityLogData,
    ): EntityLog {
        $entityClassName = $this->entityNameResolver->resolve(EntityLog::class);

        return new $entityClassName($entityLogData);
    }
}
