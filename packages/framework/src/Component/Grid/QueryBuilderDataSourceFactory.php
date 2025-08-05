<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\SortableNullsWalker;

class QueryBuilderDataSourceFactory
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $rowIdSourceColumnName
     * @param string|null $hint
     * @return \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource
     */
    public function create(
        QueryBuilder $queryBuilder,
        string $rowIdSourceColumnName,
        ?string $hint = SortableNullsWalker::class,
    ): QueryBuilderDataSource {
        return new QueryBuilderDataSource($queryBuilder, $rowIdSourceColumnName, $hint);
    }
}
