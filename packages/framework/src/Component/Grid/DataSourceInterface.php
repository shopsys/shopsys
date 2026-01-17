<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

interface DataSourceInterface
{
    public const string ORDER_ASC = 'asc';
    public const string ORDER_DESC = 'desc';

    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        string $orderDirection = self::ORDER_ASC,
    ): PaginationResult;

    public function getOneRow(int|string $rowId): array;

    public function getTotalRowsCount(): int;

    public function getRowIdSourceColumnName(): string;
}
