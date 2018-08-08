<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

interface DataSourceInterface
{
    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    /**
     * @param int|null $limit
     * @param string|null $orderSourceColumnName
     */
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        string $orderDirection = self::ORDER_ASC
    ): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
    
    public function getOneRow(int $rowId): array;

    public function getTotalRowsCount(): int;

    public function getRowIdSourceColumnName(): string;
}
