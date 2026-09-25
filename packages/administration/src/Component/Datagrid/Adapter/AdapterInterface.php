<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter;

use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCapabilitiesInterface;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

/**
 * Loads the records of a datagrid from one medium.
 *
 * The adapter keeps no state between listings: what belongs to the medium itself — a fixed scope, a default
 * join — is given to it once when it is created, and everything one listing asks for arrives in its
 * `DatasourceRequest`. Building the data source is therefore a pure function of the request, which is what
 * lets a datagrid render its view any number of times and two datagrids share one adapter.
 *
 * The condition of the request is data of the shared vocabulary; the adapter compiles it with the builder
 * of its own medium while building the data source, so an expression never comes from a foreign medium and
 * the medium is never prepared (an association joined, a value registered) for nothing.
 *
 * Optional abilities of a medium are declared by the extending interfaces (`EntityClassAwareAdapterInterface`,
 * `PathDescribingAdapterInterface`), so a caller asks for them by `instanceof` and degrades gracefully
 * when the medium lacks them.
 */
interface AdapterInterface
{
    /**
     * Tells what the medium is able to express, so that a declaration can be checked before anything is built.
     */
    public function getExpressionCapabilities(): ExpressionCapabilitiesInterface;

    /**
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\ConditionNotSupportedException
     */
    public function getDatasource(DatasourceRequest $request): DataSourceInterface;
}
