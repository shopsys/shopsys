<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter;

use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;

/**
 * Implemented by adapters that know the schema of their records and can therefore describe a path before any
 * query is built.
 *
 * A declaration uses it to fail fast — a searchable field has to hold text, a filter has to address a path
 * that exists and offer only the operations applicable to it. An adapter without a schema (records already in
 * memory) cannot implement it, so a caller has to degrade to checking at evaluation time.
 */
interface PathDescribingAdapterInterface extends AdapterInterface
{
    /**
     * Describes what the path leads to, without any side effect on the records or the query.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException
     */
    public function describePath(string $path): PathDescription;
}
