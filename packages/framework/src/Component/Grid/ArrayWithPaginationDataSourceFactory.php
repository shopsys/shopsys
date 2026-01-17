<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

class ArrayWithPaginationDataSourceFactory
{
    /**
     * @param array<int|string, mixed> $data
     */
    public function create(array $data, ?string $rowIdSourceColumnName = null): ArrayWithPaginationDataSource
    {
        return new ArrayWithPaginationDataSource($data, $rowIdSourceColumnName);
    }
}
