<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

/**
 * Walks a dot notation path through the entity mapping without building any query.
 *
 * A project reads a path differently (for example the value type of its own Doctrine type) by its own implementation
 * registered in place of `PathWalker`, typically decorating it.
 */
interface PathWalkerInterface
{
    /**
     * Throws `PathNotFoundException` when the mapping has no field or association on the path.
     *
     * @param class-string $entityClass
     */
    public function walk(string $entityClass, string $path): WalkedPath;
}
