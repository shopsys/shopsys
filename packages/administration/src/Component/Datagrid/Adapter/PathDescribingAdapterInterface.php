<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter;

use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;

/**
 * An adapter that knows the schema of its records, so it describes a path before any query is built.
 */
interface PathDescribingAdapterInterface extends AdapterInterface
{
    /**
     * Describes what the path leads to, without any side effect on the records or the query.
     * Throws `PathNotFoundException` when the path leads nowhere.
     */
    public function describePath(string $path): PathDescription;
}
