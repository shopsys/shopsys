<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;

/**
 * A project uses its own builder (for example one taught a new operation) by its own implementation registered
 * in place of `DqlExpressionBuilderFactory`.
 */
interface DqlExpressionBuilderFactoryInterface
{
    public function create(ProxyQuery $proxyQuery): DqlExpressionBuilder;
}
