<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Closure;
use Doctrine\Persistence\ManagerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderFactoryInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Grid\HintsHelper;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

final class OrmAdapterFactory
{
    public function __construct(
        private readonly EntityNameResolver $entityNameResolver,
        private readonly ManagerRegistry $managerRegistry,
        private readonly Localization $localization,
        private readonly HintsHelper $hintsHelper,
        private readonly CrudEntityIdentifierExtractor $crudEntityIdentifierExtractor,
        private readonly DqlExpressionBuilderFactoryInterface $dqlExpressionBuilderFactory,
        private readonly PathWalkerInterface $pathWalker,
    ) {
    }

    /**
     * @param class-string $entityClass
     * @param null|\Closure(\Doctrine\ORM\QueryBuilder): void $configureQuery shapes the base query — fixed conditions, joins, computed columns, ordering
     */
    public function create(string $entityClass, ?Closure $configureQuery = null): OrmAdapter
    {
        return new OrmAdapter(
            $this->entityNameResolver->resolve($entityClass),
            $this->managerRegistry,
            $this->localization,
            $this->hintsHelper,
            $this->crudEntityIdentifierExtractor,
            $this->dqlExpressionBuilderFactory,
            $this->pathWalker,
            $configureQuery,
        );
    }
}
