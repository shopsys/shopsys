<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Path;

/**
 * The least and the greatest integer the adapter stores on a path, both inclusive.
 */
final readonly class IntegerRange
{
    public function __construct(
        public int $minimum,
        public int $maximum,
    ) {
    }
}
