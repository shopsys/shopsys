<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role\Hierarchy;

use InvalidArgumentException;
use Override;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Context-aware role hierarchy that switches Role hierarchy based on the current request context.
 */
final class ContextAwareRoleHierarchy implements RoleHierarchyInterface
{
    private ?RoleHierarchy $roleHierarchy = null;

    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\Security\Role\Hierarchy\AbstractRoleHierarchyProvider>
     */
    private array $providers;

    /**
     * @param iterable<string, \Shopsys\FrameworkBundle\Component\Security\Role\Hierarchy\AbstractRoleHierarchyProvider> $hierarchyProviders
     */
    public function __construct(
        #[TaggedIterator('shopsys.role_hierarchy_provider')]
        iterable $hierarchyProviders,
        private readonly ContextResolverInterface $contextResolver,
    ) {
        foreach ($hierarchyProviders as $provider) {
            if (!$provider instanceof AbstractRoleHierarchyProvider) {
                throw new InvalidArgumentException(sprintf(
                    'Expected instance of %s, got %s',
                    AbstractRoleHierarchyProvider::class,
                    get_debug_type($provider),
                ));
            }

            $this->contextResolver->validateContextClass($provider->getTargetContext());
            $this->providers[$provider->getTargetContext()] = $provider;
        }
    }

    /**
     * @param string[] $roles
     * @return string[]
     */
    #[Override]
    public function getReachableRoleNames(array $roles): array
    {
        return $this->getHierarchyForCurrentContext()->getReachableRoleNames($roles);
    }

    /**
     * Get hierarchy for current request context
     */
    private function getHierarchyForCurrentContext(): RoleHierarchy
    {
        if ($this->roleHierarchy !== null) {
            return $this->roleHierarchy;
        }

        $matchedRoleHierarchy = [];

        foreach ($this->providers as $provider) {
            if ($this->contextResolver->isCurrentContext($provider->getTargetContext())) {
                $matchedRoleHierarchy = array_merge($matchedRoleHierarchy, $provider->generateRoleHierarchy());
            }
        }

        $this->roleHierarchy = new RoleHierarchy($matchedRoleHierarchy);

        return $this->roleHierarchy;
    }
}
