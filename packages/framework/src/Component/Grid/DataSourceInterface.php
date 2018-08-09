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
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedRows(
        $limit = null,
        $page = 1,
        $orderSourceColumnName = null,
        $orderDirection = self::ORDER_ASC
    );

    /**
     * @param int $rowId
     * @return array
     */
    public function getOneRow($rowId);

    public function getTotalRowsCount();

    public function getRowIdSourceColumnName();
}
