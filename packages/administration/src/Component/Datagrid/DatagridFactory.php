<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\EntityClassAwareAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScopeFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Narrowing\DatagridNarrowingResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @phpstan-type DatagridOptions array{
 *     name?: string,
 *     crudDefinition?: \Shopsys\AdministrationBundle\Component\Crud\Definition|null,
 *     pagination?: bool,
 *     roleConstant: string,
 * }
 */
final class DatagridFactory
{
    public function __construct(
        private readonly GridFactory $gridFactory,
        private readonly AccessCheckerInterface $accessChecker,
        private readonly AdministratorGridFacade $administratorGridFacade,
        private readonly Security $security,
        private readonly DomainControlScopeFactory $domainControlScopeFactory,
        private readonly DatagridNarrowingResolver $datagridNarrowingResolver,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * The state of the datagrid is read from the request and its domain control from the CRUD definition
     * unless given explicitly, which is how a datagrid is built in a test or outside a request.
     *
     * @param DatagridOptions $options
     */
    public function create(
        AdapterInterface $adapter,
        array $options,
        ?DatagridRequestState $requestState = null,
        ?DomainControlScope $domainControlScope = null,
    ): Datagrid {
        return new Datagrid(
            $adapter,
            $this->gridFactory,
            $this->accessChecker,
            $this->administratorGridFacade,
            $this->security,
            $this->datagridNarrowingResolver,
            $requestState ?? $this->createRequestState($options['name'] ?? 'datagrid'),
            $domainControlScope ?? $this->createDomainControlScope($adapter, $options['crudDefinition'] ?? null),
            $options,
        );
    }

    private function createRequestState(string $gridId): DatagridRequestState
    {
        $request = $this->requestStack->getMainRequest();

        return $request === null ? DatagridRequestState::outsideRequest($gridId) : DatagridRequestState::fromRequest($request, $gridId);
    }

    /**
     * The domain control of a CRUD datagrid follows its configuration; a datagrid outside CRUD has none.
     */
    private function createDomainControlScope(
        AdapterInterface $adapter,
        ?Definition $crudDefinition,
    ): ?DomainControlScope {
        if ($crudDefinition === null) {
            return null;
        }

        return $this->domainControlScopeFactory->create(
            $crudDefinition->getConfig()->getDomainControlConfig(),
            $adapter instanceof EntityClassAwareAdapterInterface ? $adapter->getEntityClass() : null,
        );
    }
}
