<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory;

final class ArrayAdapterFactory
{
    public function __construct(
        private readonly ArrayWithPaginationDataSourceFactory $arrayWithPaginationDataSourceFactory,
    ) {
    }

    public function create(array $data): ArrayAdapter
    {
        return new ArrayAdapter($data, $this->arrayWithPaginationDataSourceFactory);
    }
}
