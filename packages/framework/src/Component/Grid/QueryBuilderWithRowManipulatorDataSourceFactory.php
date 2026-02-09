<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\QueryBuilder;

class QueryBuilderWithRowManipulatorDataSourceFactory
{
    public function __construct(
        protected readonly HintsHelper $hintsHelper,
    ) {
    }

    /**
     * @param array<string, mixed>|null $hints
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
