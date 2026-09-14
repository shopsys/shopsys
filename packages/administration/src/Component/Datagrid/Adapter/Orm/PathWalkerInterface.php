<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

/**
 * Walks a dot notation path through the entity mapping without touching any query — the single place
 * knowing how a path of the ORM adapter is read and what it leads to.
 *
 * It is the seam a project uses to read a path differently (a value type of an own Doctrine type, ...) —
 * an own implementation replaces this service in the DI container.
 */
interface PathWalkerInterface
{
    /**
     * @param class-string $entityClass
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException
     */
    public function walk(string $entityClass, string $path): WalkedPath;
}
