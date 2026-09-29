<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCapabilitiesInterface;

/**
 * Creates the builder of the expressions of one Doctrine query. The factory is the entry point of the DQL
 * medium: it says what the medium expresses and it binds a builder to a query on demand.
 *
 * It is the seam a project uses to extend or change the medium — an own factory returning an own builder
 * (typically wrapping the default one) replaces this service, and the adapter never knows.
 */
interface DqlExpressionBuilderFactoryInterface extends ExpressionCapabilitiesInterface
{
    public function create(ProxyQuery $proxyQuery): DqlExpressionBuilderInterface;
}
