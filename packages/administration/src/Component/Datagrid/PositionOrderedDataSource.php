<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Override;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

/**
 * Lists the records of a datagrid ordered by drag-and-drop by their position whenever the grid asks for no order of
 * its own. The grid orders them so only while they can be dragged, while the order holds when they cannot — for
 * an administrator without the permission to edit them, or over records narrowed by the administrator.
 */
class PositionOrderedDataSource implements DataSourceInterface
{
    public function __construct(
        protected readonly DataSourceInterface $innerDataSource,
        protected readonly string $positionSourceColumnName,
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
