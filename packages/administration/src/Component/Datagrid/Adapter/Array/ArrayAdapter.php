<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

use Closure;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ExpressionFromForeignMediumException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCapabilitiesInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompilerInterface;
use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

final class ArrayAdapter implements AdapterInterface
{
    public function __construct(
        private readonly array $data,
        private readonly ArrayWithPaginationDataSourceFactory $arrayWithPaginationDataSourceFactory,
        private readonly PhpExpressionBuilder $phpExpressionBuilder,
        private readonly ExpressionCompilerInterface $expressionCompiler,
    ) {
    }

    /**
     * The builder is a stateless service, so it answers for the medium as a whole.
     */
    #[Override]
    public function getExpressionCapabilities(): ExpressionCapabilitiesInterface
    {
        return $this->phpExpressionBuilder;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDatasource(DatasourceRequest $request): DataSourceInterface
    {
        $data = $this->data;

        if ($request->condition !== null) {
            $data = array_filter($data, $this->compileCondition($request));
        }

        return $this->arrayWithPaginationDataSourceFactory->create(array_values($data), $request->identificationName);
    }

    /**
     * @return \Closure(array<string, mixed> $row): bool
     */
    private function compileCondition(DatasourceRequest $request): Closure
    {
        $expression = $this->expressionCompiler->compile($this->phpExpressionBuilder, $request->condition);

        if ($expression instanceof Closure === false) {
            throw new ExpressionFromForeignMediumException(Closure::class, $expression, self::class);
        }

        return $expression;
    }
}
