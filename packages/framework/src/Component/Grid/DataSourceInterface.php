<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

interface DataSourceInterface
{
    public const ORDER_ASC = 'asc';
    public const ORDER_DESC = 'desc';

    /**
     * @param int|null $limit
     * @param int $page
     * @param string|null $orderSourceColumnName
     * @param string $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedRows(
        $limit = null,
        $page = 1,
        $orderSourceColumnName = null,
        $orderDirection = self::ORDER_ASC,
    ): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

    /**
     * @param int $rowId
     * @return mixed[]
     */
    public function getOneRow($rowId): array;

    /**
     * @return int
     */
    public function getTotalRowsCount(): int;

    /**
     * @return string
     */
    public function getRowIdSourceColumnName(): string;
}
