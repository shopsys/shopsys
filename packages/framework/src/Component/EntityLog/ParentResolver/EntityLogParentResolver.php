<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ParentResolver;

use Webmozart\Assert\Assert;

class EntityLogParentResolver
{
    /**
     * @param iterable<\Shopsys\FrameworkBundle\Component\EntityLog\ParentResolver\EntityLogParentResolverInterface> $parentResolvers
     */
    public function __construct(
        protected readonly iterable $parentResolvers,
    ) {
        Assert::allIsInstanceOf($parentResolvers, EntityLogParentResolverInterface::class);
    }

    public function supports(object $entity): bool
    {
        return $this->findParentResolver($entity) !== null;
    }

    public function resolveParent(object $entity): ?object
    {
        return $this->findParentResolver($entity)?->resolveParent($entity);
    }

    protected function findParentResolver(object $entity): ?EntityLogParentResolverInterface
    {
        foreach ($this->parentResolvers as $parentResolver) {
            if ($parentResolver->supports($entity)) {
                return $parentResolver;
            }
        }

        return null;
    }
}
