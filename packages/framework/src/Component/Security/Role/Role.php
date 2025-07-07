<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

use Shopsys\FrameworkBundle\Component\Security\Role\Exception\RoleCannotBeOverwrittenException;
use Webmozart\Assert\Assert;

class Role
{
    protected bool $allowOverwrite = true;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Security\Role\Permission[]
     */
    protected array $highestLevelPermissions;

    /**
     * @param string $constant
     * @param string $name Human-readable name
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $availablePermissions
     * @param bool $allowOverwrite Whether the role can be modified after creation
     */
    public function __construct(
        protected readonly string $constant,
        protected string $name,
        protected array $availablePermissions = [],
        bool $allowOverwrite = true,
    ) {
        $this->highestLevelPermissions = Permission::getHighestLevelPermissions($this->availablePermissions);
        $this->setOverwritable($allowOverwrite);
    }

    /**
     * @return string
     */
    public function getConstant(): string
    {
        return $this->constant;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Permission[]
     */
    public function getAvailablePermissions(): array
    {
        $availablePermissions = [];

        foreach ($this->getHighestLevelPermissions() as $permission) {
            $availablePermissions[] = $permission;

            foreach ($permission->getSubordinatePermissions() as $subordinatePermission) {
                if (!in_array($subordinatePermission, $availablePermissions, true)) {
                    $availablePermissions[] = $subordinatePermission;
                }
            }
        }

        return $availablePermissions;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Permission $permission
     * @return bool
     */
    public function hasPermission(Permission $permission): bool
    {
        foreach ($this->getHighestLevelPermissions() as $highestLevelPermission) {
            if ($highestLevelPermission->includes($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        if ($this->isOverwritable() === false) {
            throw new RoleCannotBeOverwrittenException($this);
        }

        $this->name = $name;
    }

    /**
     * @param bool $overwritable
     */
    public function setOverwritable(bool $overwritable): void
    {
        if ($this->isOverwritable() === false) {
            throw new RoleCannotBeOverwrittenException($this);
        }

        $this->allowOverwrite = $overwritable;
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $availablePermissions
     */
    public function setAvailablePermissions(array $availablePermissions): void
    {
        if ($this->isOverwritable() === false) {
            throw new RoleCannotBeOverwrittenException($this);
        }

        Assert::allIsInstanceOf($availablePermissions, Permission::class);

        $this->availablePermissions = $availablePermissions;
        $this->highestLevelPermissions = Permission::getHighestLevelPermissions($availablePermissions);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Permission $permission
     */
    public function addPermission(Permission $permission): void
    {
        if ($this->isOverwritable() === false) {
            throw new RoleCannotBeOverwrittenException($this);
        }

        if (in_array($permission, $this->availablePermissions, true)) {
            return;
        }

        $this->availablePermissions[] = $permission;
        $this->highestLevelPermissions = Permission::getHighestLevelPermissions($this->availablePermissions);
    }

    /**
     * Determine if the role should include the FULL permission
     *
     * The FULL permission is included if:
     * - The role does not already have FULL permission
     * - The role has more than just VIEW permission
     *
     * This is because backward compatibility. User could have ROLE_*_FULL but if available permissions do not include FULL,
     * administrator would not be able to reach the full permission in the UI.
     *
     * @return bool
     */
    public function shouldIncludeFullPermission(): bool
    {
        if ($this->isSingleRole()) {
            return false;
        }

        // If the role has FULL permission, it should not include it again
        if ($this->hasFullPermission()) {
            return false;
        }

        // If the role has only VIEW permission, it should not include FULL
        return !(count($this->getHighestLevelPermissions()) === 1 && in_array(Permission::VIEW, $this->getHighestLevelPermissions(), true));
    }

    /**
     * @return bool
     */
    public function isSingleRole(): bool
    {
        return count($this->availablePermissions) === 0;
    }

    /**
     * @return bool
     */
    public function isOverwritable(): bool
    {
        return $this->allowOverwrite;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Permission[] $permissions
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Permission[]
     */
    public function calculateHighestValidPermissions(array $permissions): array
    {
        $validPermissions = [];

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                $validPermissions[] = $permission;
            }
        }

        if (count($validPermissions) === 0) {
            return [];
        }

        $highestPermissions = Permission::getHighestLevelPermissions($validPermissions);

        if ($this->hasFullPermission() && !in_array(Permission::FULL, $highestPermissions, true)) {
            $subordinatePermissionsForFull = Permission::FULL->getSubordinatePermissions(true);
            $canTransformToFullPermission = true;

            foreach ($subordinatePermissionsForFull as $subordinatePermission) {
                if (!in_array($subordinatePermission, $highestPermissions, true)) {
                    $canTransformToFullPermission = false;

                    break;
                }
            }

            if ($canTransformToFullPermission) {
                return [Permission::FULL];
            }
        }

        return $highestPermissions;
    }

    /**
     * @return bool
     */
    protected function hasFullPermission(): bool
    {
        return in_array(Permission::FULL, $this->getHighestLevelPermissions(), true);
    }

    /**
     * Get the highest-level permissions available to this role
     *
     * @see Permission::getHighestLevelPermissions() for details on how these are determined
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Permission[]
     */
    public function getHighestLevelPermissions(): array
    {
        return $this->highestLevelPermissions;
    }
}
