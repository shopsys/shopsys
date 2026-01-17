<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use Override;
use RuntimeException;

/**
 * @implements \IteratorAggregate<string, \Shopsys\FrameworkBundle\Component\Security\Role\Role>
 */
final class RoleCollection implements IteratorAggregate
{
    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\Security\Role\Role>
     */
    private array $roles = [];

    /**
     * @throws \RuntimeException If role already exists
     */
    public function add(Role $role): void
    {
        $constant = $role->getConstant();

        if (isset($this->roles[$constant])) {
            throw new RuntimeException(
                sprintf('Role with constant "%s" already exists. Use edit() method to modify existing roles.', $constant),
            );
        }

        $this->roles[$constant] = $role;
    }

    /**
     * @param \Closure(\Shopsys\FrameworkBundle\Component\Security\Role\Role): void $closure Function that receives the role and can modify it
     * @throws \RuntimeException If role doesn't exist
     * @example
     * $collection->edit('ROLE_ORDER', function(Role $role) {
     *     $role->setName('Enhanced Orders');
     *     $role->setAvailablePermissions([Permission::VIEW, Permission::EDIT]);
     * });
     */
    public function edit(string $constant, Closure $closure): void
    {
        if (!isset($this->roles[$constant])) {
            throw new RuntimeException(sprintf('Role with constant "%s" does not exist.', $constant));
        }

        $closure($this->roles[$constant]);
    }

    public function has(string $constant): bool
    {
        return isset($this->roles[$constant]);
    }

    /**
     * @throws \RuntimeException If role with the given constant does not exist
     */
    public function get(string $constant): Role
    {
        return $this->roles[$constant] ?? throw new RuntimeException(sprintf('Role with constant "%s" does not exist.', $constant));
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    public function all(): array
    {
        return array_values($this->roles);
    }

    /**
     * @return \ArrayIterator<string, \Shopsys\FrameworkBundle\Component\Security\Role\Role>
     */
    #[Override]
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->roles);
    }

    /**
     * Locks all roles in the collection, preventing any further modifications.
     * This is useful for ensuring that roles are immutable after initial setup.
     */
    public function lockRoles(): void
    {
        foreach ($this->roles as $role) {
            if ($role->isOverwritable()) {
                $role->setOverwritable(false);
            }
        }
    }
}
