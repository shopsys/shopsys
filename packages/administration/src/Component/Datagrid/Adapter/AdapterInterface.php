<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter;

use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

/**
 * Loads the records of a datagrid from one source:
 *
 * - it keeps no state between listings, everything one listing asks for arrives in its `DatasourceRequest`;
 * - it applies `$request->condition`, which carries the domain limit of the list as well, with the meaning
 *   of each operation described on `ExpressionOperatorEnum`;
 * - it throws an exception when it cannot express the condition, instead of ignoring it.
 */
interface AdapterInterface
{
    public function getDatasource(DatasourceRequest $request): DataSourceInterface;
}
