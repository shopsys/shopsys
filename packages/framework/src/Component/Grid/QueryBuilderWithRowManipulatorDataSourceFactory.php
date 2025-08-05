<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\SortableNullsWalker;

class QueryBuilderWithRowManipulatorDataSourceFactory
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $rowIdSourceColumnName
     * @param callable $manipulateRowCallback
     * @param string|null $hint
     * @return \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource
     */
    public function create(
        QueryBuilder $queryBuilder,
        string $rowIdSourceColumnName,
        callable $manipulateRowCallback,
        ?string $hint = SortableNullsWalker::class,
    ): QueryBuilderWithRowManipulatorDataSource {
        return new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            $rowIdSourceColumnName,
            $manipulateRowCallback,
            $hint,
        );
    }
}
