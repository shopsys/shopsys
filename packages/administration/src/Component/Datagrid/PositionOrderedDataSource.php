<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Override;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

/**
 * Orders the records of a drag-and-drop datagrid by their position whenever the grid asks for no order of its own.
 *
 * The grid orders by the position only while the rows can be dragged. The order holds even when they cannot:
 *
 * - for an administrator without the permission to edit the records;
 * - for records narrowed by the administrator.
 */
final readonly class PositionOrderedDataSource implements DataSourceInterface
{
    public function __construct(
        private DataSourceInterface $innerDataSource,
        private string $positionSourceColumnName,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        string $orderDirection = self::ORDER_ASC,
    ): PaginationResult {
        if ($orderSourceColumnName === null) {
            return $this->innerDataSource->getPaginatedRows($limit, $page, $this->positionSourceColumnName, self::ORDER_ASC);
        }

        return $this->innerDataSource->getPaginatedRows($limit, $page, $orderSourceColumnName, $orderDirection);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getOneRow(int|string $rowId): array
    {
        return $this->innerDataSource->getOneRow($rowId);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTotalRowsCount(): int
    {
        return $this->innerDataSource->getTotalRowsCount();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRowIdSourceColumnName(): string
    {
        return $this->innerDataSource->getRowIdSourceColumnName();
    }
}
