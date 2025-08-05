<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

class ArrayDataSourceFactory
{
    /**
     * @param array<int|string, mixed> $data
     * @param string|null $rowIdSourceColumnName
     * @return \Shopsys\FrameworkBundle\Component\Grid\ArrayDataSource
     */
    public function create(array $data, ?string $rowIdSourceColumnName = null): ArrayDataSource
    {
        return new ArrayDataSource($data, $rowIdSourceColumnName);
    }
}
