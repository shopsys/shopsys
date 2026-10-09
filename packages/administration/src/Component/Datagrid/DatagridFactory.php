<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Symfony\Bundle\SecurityBundle\Security;

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
    ) {
    }

    /**
     * @param DatagridOptions $options
     */
    public function create(AdapterInterface $adapter, array $options): Datagrid
    {
        return new Datagrid($adapter, $this->gridFactory, $this->accessChecker, $this->administratorGridFacade, $this->security, $options);
    }
}
