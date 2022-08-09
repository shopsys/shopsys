<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

/**
 * @template T of array<string, mixed>
 */
interface DataSourceInterface
{
    public const ORDER_ASC = 'asc';
    public const ORDER_DESC = 'desc';

    /**
     * @param int|null $limit
     * @param int $page
     * @param string|null $orderSourceColumnName
     * @param string|null $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult<T>
     */
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        ?string $orderDirection = self::ORDER_ASC
    ): PaginationResult;

    /**
     * @param int $rowId
     * @return T
     */
    public function getOneRow(int $rowId): array;

    /**
     * @return int
     */
    public function getTotalRowsCount(): int;

    /**
     * @return string
     */
    public function getRowIdSourceColumnName(): string;
}
