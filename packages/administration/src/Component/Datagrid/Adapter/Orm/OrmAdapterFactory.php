<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Closure;
use Doctrine\Persistence\ManagerRegistry;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Grid\HintsHelper;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

final class OrmAdapterFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Doctrine\Persistence\ManagerRegistry $managerRegistry
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Component\Grid\HintsHelper $hintsHelper
     */
    public function __construct(
        private readonly EntityNameResolver $entityNameResolver,
        private readonly ManagerRegistry $managerRegistry,
        private readonly Localization $localization,
        private readonly HintsHelper $hintsHelper,
    ) {
    }

    /**
     * @param class-string $entityClass FQCN of entity. Entity class will be resolved by EntityNameResolver inside the adapter.
     * @param null|\Closure(\Doctrine\ORM\QueryBuilder): void $configureQuery
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapter
     */
    public function create(string $entityClass, ?Closure $configureQuery = null): OrmAdapter
    {
        return new OrmAdapter(
            $this->entityNameResolver->resolve($entityClass),
            $this->managerRegistry,
            $this->localization,
            $this->hintsHelper,
            $configureQuery,
        );
    }
}
