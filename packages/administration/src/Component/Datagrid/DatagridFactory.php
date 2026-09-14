<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\EntityClassAwareAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScopeFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;

/**
 * @phpstan-type DatagridOptions array{
 *     name?: string,
 *     crudDefinition?: \Shopsys\AdministrationBundle\Component\Crud\Definition|null,
 *     pagination?: bool,
 *     domainControlScope?: \Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope|null,
 *     roleConstant: string,
 * }
 */
final class DatagridFactory
{
    public function __construct(
        private readonly GridFactory $gridFactory,
        private readonly DomainControlScopeFactory $domainControlScopeFactory,
    ) {
    }

    /**
     * @param DatagridOptions $options
     */
    public function create(AdapterInterface $adapter, array $options): Datagrid
    {
        return new Datagrid($adapter, $this->gridFactory, $this->resolveDomainControlScope($adapter, $options));
    }

    /**
     * Resolves the domain control of a CRUD datagrid from its configuration, unless the scope is given explicitly.
     *
     * @param DatagridOptions $options
     * @return DatagridOptions
     */
    private function resolveDomainControlScope(AdapterInterface $adapter, array $options): array
    {
        $crudDefinition = $options['crudDefinition'] ?? null;

        if (($options['domainControlScope'] ?? null) !== null || $crudDefinition === null) {
            return $options;
        }

        $options['domainControlScope'] = $this->domainControlScopeFactory->create(
            $crudDefinition->getConfig()->getDomainControlConfig(),
            $adapter instanceof EntityClassAwareAdapterInterface ? $adapter->getEntityClass() : null,
        );

        return $options;
    }
}
