<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;

/**
 * Creates the builder of the expressions of one Doctrine query — the entry point of the DQL medium, binding
 * a builder to a query on demand.
 *
 * It is the seam a project uses to extend or change the medium — an own factory returning an own builder
 * replaces this service in the DI container, and the adapter never knows.
 */
interface DqlExpressionBuilderFactoryInterface
{
    public function create(ProxyQuery $proxyQuery): DqlExpressionBuilderInterface;
}
