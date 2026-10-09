<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ParentResolver;

/**
 * Finds the parent of a logged child that has no association to its parent (e.g. images referencing their entity by name and ID)
 */
interface EntityLogParentResolverInterface
{
    public function supports(object $entity): bool;

    public function resolveParent(object $entity): ?object;
}
