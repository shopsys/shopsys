<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;

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
    ) {
    }

    /**
     * @param DatagridOptions $options
     */
    public function create(AdapterInterface $adapter, array $options): Datagrid
    {
        return new Datagrid($adapter, $this->gridFactory, $options);
    }
}
