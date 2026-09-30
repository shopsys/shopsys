<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatagridRowProcessor;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

final class ArrayAdapter implements AdapterInterface
{
    /**
     * @param mixed[][] $data
     */
    public function __construct(
        private readonly array $data,
    ) {
    }

    /**
     * @param array<\Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor> $fields
     */
    #[Override]
    public function getDatasource(string $identificationName, array $fields): DataSourceInterface
    {
        $propertyPaths = [];

        foreach ($fields as $field) {
            $propertyPaths = [...$propertyPaths, ...$field->getSelectProperties()];
        }

        return new ArrayDatagridDataSource(
            $this->data,
            $identificationName,
            new DatagridRowProcessor($fields),
            array_unique($propertyPaths),
        );
    }
}
