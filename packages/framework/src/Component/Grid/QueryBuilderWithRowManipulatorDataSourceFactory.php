<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\QueryBuilder;

class QueryBuilderWithRowManipulatorDataSourceFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\HintsHelper $hintsHelper
     */
    public function __construct(
        protected readonly HintsHelper $hintsHelper,
    ) {
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $rowIdSourceColumnName
     * @param callable $manipulateRowCallback
     * @param array<string, mixed>|null $hints
     * @return \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource
     */
    public function create(
        QueryBuilder $queryBuilder,
        string $rowIdSourceColumnName,
        callable $manipulateRowCallback,
        ?array $hints = null,
    ): QueryBuilderWithRowManipulatorDataSource {
        return new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            $rowIdSourceColumnName,
            $manipulateRowCallback,
            $hints ?? $this->hintsHelper->getDefaultHints(),
        );
    }
}
