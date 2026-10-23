<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface;
use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

final class ArrayAdapter implements AdapterInterface
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<\Closure> $expressionBuilder The builder of the in-memory medium, whose expression decides a single row
     */
    public function __construct(
        private readonly array $data,
        private readonly ArrayWithPaginationDataSourceFactory $arrayWithPaginationDataSourceFactory,
        private readonly ExpressionBuilderInterface $expressionBuilder,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDatasource(DatasourceRequest $request): DataSourceInterface
    {
        $data = $this->data;

        if ($request->condition !== null) {
            $data = array_filter($data, $this->expressionBuilder->compile($request->condition));
        }

        return $this->arrayWithPaginationDataSourceFactory->create(array_values($data), $request->identificationName);
    }
}
