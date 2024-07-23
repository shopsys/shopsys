<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class ArrayWithPaginationDataSource extends ArrayDataSource
{
    /**
     * {@inheritdoc}
     */
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        string $orderDirection = self::ORDER_ASC,
    ): PaginationResult {
        $offset = (int)(($page - 1) * $limit);
        $orderSourceColumnNameArray = array_column($this->data, $orderSourceColumnName);

        array_multisort(
            $orderSourceColumnNameArray,
            $orderDirection === self::ORDER_ASC ? SORT_ASC : SORT_DESC,
            $this->data,
        );

        $data = array_slice($this->data, $offset, $limit);

        return new PaginationResult($page, $limit, count($this->data), $data);
    }
}
