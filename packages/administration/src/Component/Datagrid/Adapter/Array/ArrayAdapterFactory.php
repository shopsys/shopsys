<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompilerInterface;
use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory;

final class ArrayAdapterFactory
{
    public function __construct(
        private readonly ArrayWithPaginationDataSourceFactory $arrayWithPaginationDataSourceFactory,
        private readonly PhpExpressionBuilder $phpExpressionBuilder,
        private readonly ExpressionCompilerInterface $expressionCompiler,
    ) {
    }

    public function create(array $data): ArrayAdapter
    {
        return new ArrayAdapter($data, $this->arrayWithPaginationDataSourceFactory, $this->phpExpressionBuilder, $this->expressionCompiler);
    }
}
