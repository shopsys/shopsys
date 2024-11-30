<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter;

use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

interface AdapterInterface
{
    /**
     * @param string $identificationName
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     * @return \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface
     */
    public function getDatasource(string $identificationName, array $fields): DataSourceInterface;
}
