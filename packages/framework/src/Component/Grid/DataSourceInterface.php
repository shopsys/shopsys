<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

interface DataSourceInterface
{
    public const string ORDER_ASC = 'asc';
    public const string ORDER_DESC = 'desc';

    /**
     * @param int|null $limit
     * @param int $page
     * @param string|null $orderSourceColumnName
     * @param string $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        string $orderDirection = self::ORDER_ASC,
    ): PaginationResult;

    /**
     * @param int $rowId
     * @return array
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
