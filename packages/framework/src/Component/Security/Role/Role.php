<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

use Shopsys\FrameworkBundle\Component\ArrayUtils\ArrayHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\Exception\RoleCannotBeOverwrittenException;
use Webmozart\Assert\Assert;

class Role
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Security\Role\Permission[]
     */
    protected array $expandedPermissions;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Security\Role\Permission[]
     */
    protected array $highestLevelPermissions;

    protected ?string $roleSection = null;

    /**
     * @param string $name Human-readable name
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $availablePermissions
     * @param bool $allowOverwrite Whether the role can be modified after creation
     */
    public function __construct(
        protected readonly string $constant,
        protected string $name,
        array $availablePermissions = [],
        protected bool $allowOverwrite = true,
    ) {
        $this->calculatePermissions($availablePermissions);
    }

    public function getConstant(): string
    {
        return $this->constant;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Permission[]
     */
    public function getAvailablePermissions(): array
    {
        return $this->expandedPermissions;
    }

    public function hasPermission(Permission $permission): bool
    {
        return in_array($permission, $this->getAvailablePermissions(), true);
    }

    public function setName(string $name): void
    {
        if ($this->isOverwritable() === false) {
            throw new RoleCannotBeOverwrittenException($this);
        }

        $this->name = $name;
    }

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

        $this->calculatePermissions($availablePermissions);
    }

    public function addPermission(Permission $permission): void
    {
        if ($this->isOverwritable() === false) {
            throw new RoleCannotBeOverwrittenException($this);
        }

        if ($this->hasPermission($permission)) {
            return; // Permission already exists, no need to add it again
        }

        $this->setAvailablePermissions(array_merge($this->highestLevelPermissions, [$permission]));
    }

    public function getRoleSection(): ?string
    {
        return $this->roleSection;
    }

    public function setRoleSection(string $roleSection): void
    {
        if ($this->isOverwritable() === false) {
            throw new RoleCannotBeOverwrittenException($this);
        }

        $this->roleSection = $roleSection;
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
        $highest = $this->getHighestLevelPermissions();

        return !(count($highest) === 1 && $highest[0] === Permission::VIEW);
    }

    public function isSingleRole(): bool
    {
        return count($this->getAvailablePermissions()) === 0;
    }

    public function isOverwritable(): bool
    {
        return $this->allowOverwrite;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Permission[] $inputPermissions
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Permission[]
     */
    public function calculateHighestValidPermissions(array $inputPermissions): array
    {
        if ($this->isSingleRole()) {
            return [];
        }

        if (($this->shouldIncludeFullPermission() || $this->hasFullPermission()) && in_array(Permission::FULL, $inputPermissions, true)) {
            return $this->getHighestLevelPermissions();
        }

        $validPermissions = array_filter($inputPermissions, fn (Permission $p) => $this->hasPermission($p));

        if (count($validPermissions) === 0) {
            return [];
        }

        $highestPermissions = Permission::getHighestLevelPermissions($validPermissions);

        if ($this->hasFullPermission() && !in_array(Permission::FULL, $highestPermissions, true)) {
            $subordinatePermissionsForFull = Permission::FULL->getSubordinatePermissions(true);

            if (
                ArrayHelper::haveArraysDifferentValues(
                    Permission::toValues(...$subordinatePermissionsForFull),
                    Permission::toValues(...$highestPermissions),
                ) === false
            ) {
                return [Permission::FULL];
            }
        }

        return $highestPermissions;
    }

    protected function hasFullPermission(): bool
    {
        return $this->hasPermission(Permission::FULL);
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

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $availablePermissions
     */
    protected function calculatePermissions(array $availablePermissions): void
    {
        Assert::allIsInstanceOf($availablePermissions, Permission::class);

        $this->expandedPermissions = Permission::expand($availablePermissions);
        $this->highestLevelPermissions = Permission::getHighestLevelPermissions($availablePermissions);
    }
}
