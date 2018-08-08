<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

interface DataSourceInterface
{
    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    /**
     * @param int|null $limit
     * @param int $page
     * @param string|null $orderSourceColumnName
     * @param string $orderDirection
     */
    public function getPaginatedRows(
        $limit = null,
        $page = 1,
        $orderSourceColumnName = null,
        $orderDirection = self::ORDER_ASC
    ): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

    /**
     * @param int $rowId
     */
    public function getOneRow($rowId): array;

    public function getTotalRowsCount(): int;

    public function getRowIdSourceColumnName(): string;
}
