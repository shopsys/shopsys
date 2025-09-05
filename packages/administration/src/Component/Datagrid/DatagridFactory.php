<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;

/**
 * @phpstan-type DatagridOptions array{
 *     name?: string,
 *     crudConfig?: \Shopsys\AdministrationBundle\Component\Config\CrudConfigData|null,
 *     pagination?: bool,
 *     roleConstant: string,
 * }
 */
final class DatagridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     */
    public function __construct(
        private readonly GridFactory $gridFactory,
    ) {
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface $adapter
     * @param DatagridOptions $options
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid
     */
    public function create(AdapterInterface $adapter, array $options): Datagrid
    {
        return new Datagrid($adapter, $this->gridFactory, $options);
    }
}
