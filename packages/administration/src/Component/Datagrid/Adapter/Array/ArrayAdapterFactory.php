<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory;

final class ArrayAdapterFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory $arrayWithPaginationDataSourceFactory
     */
    public function __construct(
        private readonly ArrayWithPaginationDataSourceFactory $arrayWithPaginationDataSourceFactory,
    ) {
    }

    /**
     * @param array $data
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\ArrayAdapter
     */
    public function create(array $data): ArrayAdapter
    {
        return new ArrayAdapter($data, $this->arrayWithPaginationDataSourceFactory);
    }
}
