<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

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
     * @param string $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult<T>
     */
    public function getPaginatedRows(
        $limit = null,
        $page = 1,
        $orderSourceColumnName = null,
        $orderDirection = self::ORDER_ASC
    );

    /**
     * @param int $rowId
     * @return T
     */
    public function getOneRow($rowId);

    /**
     * @return int
     */
    public function getTotalRowsCount();

    /**
     * @return string
     */
    public function getRowIdSourceColumnName();
}
