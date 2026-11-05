<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface;
use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory;

final class ArrayAdapterFactory
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<\Closure> $expressionBuilder The builder of the in-memory medium, whose expression decides a single row
     */
    public function __construct(
        private readonly ArrayWithPaginationDataSourceFactory $arrayWithPaginationDataSourceFactory,
        private readonly ExpressionBuilderInterface $expressionBuilder,
    ) {
    }

    public function create(array $data): ArrayAdapter
    {
        return new ArrayAdapter($data, $this->arrayWithPaginationDataSourceFactory, $this->expressionBuilder);
    }
}
