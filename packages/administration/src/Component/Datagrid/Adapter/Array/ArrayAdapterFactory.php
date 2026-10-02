<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

final class ArrayAdapterFactory
{
    /**
     * @param mixed[][] $data
     */
    public function create(array $data): ArrayAdapter
    {
        return new ArrayAdapter($data);
    }
}
