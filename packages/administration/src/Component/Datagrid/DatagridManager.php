<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;

final class DatagridManager
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     */
    public function __construct(
        private readonly GridFactory $gridFactory,
    ) {
    }

    /**
     * @param mixed $name
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface $query
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function createGrid(mixed $name, DataSourceInterface $query): Grid
    {
        return $this->gridFactory->create($name, $query);
    }
}
