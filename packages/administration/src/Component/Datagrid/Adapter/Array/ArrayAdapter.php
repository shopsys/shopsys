<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

final class ArrayAdapter implements AdapterInterface
{
    public function __construct(
        private readonly array $data,
        private readonly ArrayWithPaginationDataSourceFactory $arrayWithPaginationDataSourceFactory,
    ) {
    }

    /**
     * @param array<\Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor> $fields
     */
    #[Override]
    public function getDatasource(string $identificationName, array $fields): DataSourceInterface
    {
        return $this->arrayWithPaginationDataSourceFactory->create($this->data, $identificationName);
    }
}
