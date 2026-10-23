<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Path;

enum PathCardinalityEnum
{
    /**
     * Every association of the path leads to at most one row, so the path leads to a single value of a record.
     */
    case TO_ONE;

    /**
     * The path leads through an association with many rows, so it leads to several values of a record.
     * A medium has to match such a path without multiplying the record by its related rows.
     */
    case TO_MANY;
}
