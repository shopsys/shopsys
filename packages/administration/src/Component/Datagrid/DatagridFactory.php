<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\EntityClassAwareAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScopeFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestStateResolver;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;

/**
 * @phpstan-type DatagridOptions array{
 *     name?: string,
 *     crudDefinition?: \Shopsys\AdministrationBundle\Component\Crud\Definition|null,
 *     pagination?: bool,
 *     domainControlScope?: \Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope|null,
 *     requestState?: \Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState|null,
 *     roleConstant: string,
 * }
 */
final class DatagridFactory
{
    public function __construct(
        private readonly GridFactory $gridFactory,
        private readonly DomainControlScopeFactory $domainControlScopeFactory,
        private readonly DatagridRequestStateResolver $datagridRequestStateResolver,
        private readonly ExpressionOperatorApplicability $expressionOperatorApplicability,
    ) {
    }

    /**
     * The state of the datagrid is read from the request unless given explicitly, which is how a datagrid
     * is built in a test or outside a request.
     *
     * @param DatagridOptions $options
     */
    public function create(AdapterInterface $adapter, array $options): Datagrid
    {
        $options = $this->resolveDomainControlScope($adapter, $options);
        $options['requestState'] ??= $this->datagridRequestStateResolver->resolve($options['name'] ?? 'datagrid');

        return new Datagrid($adapter, $this->gridFactory, $this->expressionOperatorApplicability, $options);
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
