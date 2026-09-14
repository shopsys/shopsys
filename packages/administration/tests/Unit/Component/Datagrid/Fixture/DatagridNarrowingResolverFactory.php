<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture;

use Shopsys\AdministrationBundle\Component\Datagrid\Narrowing\DatagridNarrowingResolver;

/**
 * Builds the narrowing of a datagrid the way the container does, with no container.
 */
final class DatagridNarrowingResolverFactory
{
    public static function create(): DatagridNarrowingResolver
    {
        return new DatagridNarrowingResolver();
    }
}
