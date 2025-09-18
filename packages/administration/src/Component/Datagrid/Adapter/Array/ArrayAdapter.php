<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

final class ArrayAdapter implements AdapterInterface
{
    /**
     * @param array $data
     * @param \Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory $arrayWithPaginationDataSourceFactory
     */
    public function __construct(
        private readonly array $data,
        private readonly ArrayWithPaginationDataSourceFactory $arrayWithPaginationDataSourceFactory,
    ) {
    }

    /**
     * @param string $identificationName
     * @param array<\Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor> $fields
     * @return \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface
     */
    #[Override]
    public function getDatasource(string $identificationName, array $fields): DataSourceInterface
    {
        return $this->arrayWithPaginationDataSourceFactory->create($this->data, $identificationName);
    }
}
