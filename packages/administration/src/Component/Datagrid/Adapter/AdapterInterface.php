<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter;

use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

interface AdapterInterface
{
    /**
     * @param array<\Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor> $fields
     */
    public function getDatasource(string $identificationName, array $fields): DataSourceInterface;
}
