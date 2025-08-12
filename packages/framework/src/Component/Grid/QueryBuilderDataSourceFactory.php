<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\QueryBuilder;

class QueryBuilderDataSourceFactory
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
     * @param array<string, mixed>|null $hints
     * @return \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource
     */
    public function create(
        QueryBuilder $queryBuilder,
        string $rowIdSourceColumnName,
        ?array $hints = null,
    ): QueryBuilderDataSource {
        return new QueryBuilderDataSource(
            $queryBuilder,
            $rowIdSourceColumnName,
            $hints ?? $this->hintsHelper->getDefaultHints(),
        );
    }
}
