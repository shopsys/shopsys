<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\QueryBuilder;

class QueryBuilderDataSourceFactory
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
        ?array $hints = null,
    ): QueryBuilderDataSource {
        return new QueryBuilderDataSource(
            $queryBuilder,
            $rowIdSourceColumnName,
            $hints ?? $this->hintsHelper->getDefaultHints(),
        );
    }
}
