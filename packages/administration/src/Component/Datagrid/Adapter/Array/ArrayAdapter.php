<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSource;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

final class ArrayAdapter implements AdapterInterface
{
    /**
     * @param array $data
     */
    public function __construct(
        private readonly array $data,
    ) {
    }

    /**
     * @param string $identificationName
     * @param array<\Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor> $fields
     * @return \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface
     */
    public function getDatasource(string $identificationName, array $fields): DataSourceInterface
    {
        return new ArrayWithPaginationDataSource($this->data, $identificationName);
    }
}
