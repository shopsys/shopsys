<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

use InvalidArgumentException;
use Override;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * Registry for managing roles organized by context
 */
final class RoleRegistry implements RoleRegistryInterface
{
    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection>
     */
    private array $roleCollectionsByContext = [];

    /**
     * @var array<string, array<\Shopsys\FrameworkBundle\Component\Security\Role\RoleProviderInterface>>
     */
    private array $providersByContext = [];

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\RoleProviderInterface> $allRoleProviders
     */
    public function __construct(
        #[TaggedIterator('shopsys.role_provider')]
        iterable $allRoleProviders,
        private readonly ContextResolverInterface $contextResolver,
    ) {
        foreach ($allRoleProviders as $roleProvider) {
            $this->validateProvider($roleProvider);
            $this->providersByContext[$roleProvider->getTargetContext()][] = $roleProvider;
        }

        foreach ($this->providersByContext as $context => $roleProviders) {
            $this->providersByContext[$context] = $this->sortProvidersByPriority($roleProviders);
        }
    }

    #[Override]
    public function getRole(string $roleIdentifier, string $context): Role
    {
        // first try to get the role by identifier
        try {
            return $this->getRoleCollection($context)->get($roleIdentifier);
        } catch (RuntimeException) {
            // if not found, try to parse the identifier and get the role by constant
        }

        $roleConstant = RoleIdentifierHelper::getRoleConstantFromIdentifier($roleIdentifier);

        try {
            $role = $this->getRoleCollection($context)->get($roleConstant);
        } catch (RuntimeException) {
            throw new InvalidArgumentException(
                sprintf(
                    'Role "%s" not found in role registry for context "%s"',
                    $roleConstant,
                    $context,
                ),
            );
        }

        // Validate permission if specified
        $permission = RoleIdentifierHelper::getPermissionFromIdentifier($roleIdentifier);

        if ($permission !== null && !$role->hasPermission($permission)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Role "%s" does not have permission "%s". Available permissions: %s',
                    $role->getConstant(),
                    $permission->value,
                    implode(', ', array_map(fn (Permission $p) => $p->value, $role->getAvailablePermissions())),
                ),
            );
        }

        return $role;
    }

    #[Override]
    public function getRoles(string $context): array
    {
        return $this->getRoleCollection($context)->all();
    }

    /**
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context
     */
    private function getRoleCollection(string $context): RoleCollection
    {
        $this->contextResolver->validateContextClass($context);

        if (!isset($this->roleCollectionsByContext[$context])) {
            $this->roleCollectionsByContext[$context] = $this->createRoleCollection($context);
        }

        return $this->roleCollectionsByContext[$context];
    }

    private function createRoleCollection(string $context): RoleCollection
    {
        $collection = new RoleCollection();
        $providers = $this->providersByContext[$context] ?? [];

        if (count($providers) === 0) {
            return $collection;
        }

        $this->processProviders($collection, $providers);
        $collection->lockRoles();

        return $collection;
    }

    private function validateProvider(RoleProviderInterface $provider): void
    {
        $this->contextResolver->validateContextClass($provider->getTargetContext());

        if (!$provider instanceof CoreRoleProviderInterface && $provider->getPriority() <= 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'Role provider "%s" must have a priority greater than 0.',
                    get_class($provider),
                ),
            );
        }
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\RoleProviderInterface> $providers
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\RoleProviderInterface>
     */
    private function sortProvidersByPriority(array $providers): array
    {
        usort($providers, fn ($a, $b) => $a->getPriority() <=> $b->getPriority());

        return $providers;
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\RoleProviderInterface> $providers
     */
    private function processProviders(RoleCollection $collection, array $providers): void
    {
        foreach ($providers as $provider) {
            try {
                $provider->configureRoles($collection);
            } catch (RuntimeException $e) {
                throw new RuntimeException(
                    sprintf(
                        'Error processing role provider "%s": %s',
                        get_class($provider),
                        $e->getMessage(),
                    ),
                    0,
                    $e,
                );
            }
        }
    }
}
