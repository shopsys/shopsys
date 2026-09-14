<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Narrowing;

use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;

/**
 * Resolves what narrows one datagrid in the current request — the domain control.
 *
 * Replace the service in a project to narrow differently.
 */
class DatagridNarrowingResolver
{
    public function resolve(?DomainControlScope $domainControlScope): DatagridNarrowing
    {
        return new DatagridNarrowing(
            $domainControlScope,
            condition: $domainControlScope?->createCondition(),
        );
    }
}
