<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\Persistence\ManagerRegistry;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

final class OrmAdapterFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Doctrine\Persistence\ManagerRegistry $managerRegistry
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        private readonly EntityNameResolver $entityNameResolver,
        private readonly ManagerRegistry $managerRegistry,
        private readonly Localization $localization,
    ) {
    }

    /**
     * @param class-string $entityClass FQCN of entity. Entity class will be resolved by EntityNameResolver inside the adapter.
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapter
     */
    public function create(string $entityClass): OrmAdapter
    {
        return new OrmAdapter(
            $this->entityNameResolver->resolve($entityClass),
            $this->managerRegistry,
            $this->localization,
        );
    }
}
