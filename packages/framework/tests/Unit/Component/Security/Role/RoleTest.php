<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Security\Role;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Security\Role\Exception\RoleCannotBeOverwrittenException;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;

class RoleTest extends TestCase
{
    public function testGetAvailablePermissionsEmpty(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role');

        $this->assertSame([], $role->getAvailablePermissions());
        $this->assertTrue($role->isSingleRole());
    }

    public function testGetAvailablePermissionsWithPermissions(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::VIEW, Permission::EDIT]);

        $permissions = $role->getAvailablePermissions();

        $this->assertCount(2, $permissions);
        $this->assertContains(Permission::VIEW, $permissions);
        $this->assertContains(Permission::EDIT, $permissions);
    }

    public function testGetAvailablePermissionsExpandsSubordinates(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::EDIT]);

        $permissions = $role->getAvailablePermissions();

        $this->assertCount(2, $permissions);
        $this->assertContains(Permission::EDIT, $permissions);
        $this->assertContains(Permission::VIEW, $permissions); // Subordinate of EDIT
    }

    public function testGetAvailablePermissionsWithFullPermission(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::FULL]);

        $permissions = $role->getAvailablePermissions();

        $this->assertCount(5, $permissions);
        $this->assertContains(Permission::FULL, $permissions);
        $this->assertContains(Permission::VIEW, $permissions);
        $this->assertContains(Permission::EDIT, $permissions);
        $this->assertContains(Permission::CREATE, $permissions);
        $this->assertContains(Permission::DELETE, $permissions);
    }

    public function testGetAvailablePermissionsNoDuplicates(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::EDIT, Permission::CREATE]);

        $permissions = $role->getAvailablePermissions();

        $filteredViewPermissions = array_filter($permissions, fn (Permission $p) => $p === Permission::VIEW);

        // Should have VIEW only once, even though it's included by both EDIT and CREATE as subordinate
        $this->assertSame(1, count($filteredViewPermissions));
    }

    public function testHasPermission(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::EDIT]);

        $this->assertTrue($role->hasPermission(Permission::EDIT));
        $this->assertTrue($role->hasPermission(Permission::VIEW));
        $this->assertFalse($role->hasPermission(Permission::DELETE));
        $this->assertFalse($role->hasPermission(Permission::CREATE));
    }

    public function testHasPermissionWithFull(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::FULL]);

        $this->assertTrue($role->hasPermission(Permission::FULL));
        $this->assertTrue($role->hasPermission(Permission::VIEW));
        $this->assertTrue($role->hasPermission(Permission::EDIT));
        $this->assertTrue($role->hasPermission(Permission::CREATE));
        $this->assertTrue($role->hasPermission(Permission::DELETE));
    }

    public function testSetName(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role');

        $role->setName('Updated Role');

        $this->assertSame('Updated Role', $role->getName());
    }

    public function testSetNameThrowsExceptionWhenNotOverwritable(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role');
        $role->setOverwritable(false);

        $this->expectException(RoleCannotBeOverwrittenException::class);
        $this->expectExceptionMessage('Role "Test Role" setting cannot be overwritten as this role is protected');

        $role->setName('Updated Role');
    }

    public function testSetOverwritable(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role');

        $this->assertTrue($role->isOverwritable());

        $role->setOverwritable(false);

        $this->assertFalse($role->isOverwritable());
    }

    public function testSetOverwritableThrowsExceptionWhenAlreadyNotOverwritable(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role');
        $role->setOverwritable(false);

        $this->expectException(RoleCannotBeOverwrittenException::class);

        $role->setOverwritable(true);
    }

    public function testSetAvailablePermissions(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::VIEW]);

        $role->setAvailablePermissions([Permission::EDIT, Permission::DELETE]);

        $permissions = $role->getAvailablePermissions();
        $this->assertContains(Permission::EDIT, $permissions);
        $this->assertContains(Permission::DELETE, $permissions);
        $this->assertContains(Permission::VIEW, $permissions);
    }

    public function testSetAvailablePermissionsThrowsExceptionWhenNotOverwritable(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role');
        $role->setOverwritable(false);

        $this->expectException(RoleCannotBeOverwrittenException::class);

        $role->setAvailablePermissions([Permission::EDIT]);
    }

    public function testAddPermission(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::VIEW]);

        $role->addPermission(Permission::EDIT);

        $permissions = $role->getAvailablePermissions();
        $this->assertContains(Permission::VIEW, $permissions);
        $this->assertContains(Permission::EDIT, $permissions);
    }

    public function testAddPermissionNoDuplicates(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::VIEW]);

        $role->addPermission(Permission::VIEW);

        $permissions = array_filter($role->getAvailablePermissions(), fn ($p) => $p === Permission::VIEW);
        $this->assertCount(1, $permissions);
    }

    public function testAddPermissionThrowsExceptionWhenNotOverwritable(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role');
        $role->setOverwritable(false);

        $this->expectException(RoleCannotBeOverwrittenException::class);

        $role->addPermission(Permission::EDIT);
    }

    public function testShouldIncludeFullPermissionReturnsFalseForSingleRole(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role');

        $this->assertFalse($role->shouldIncludeFullPermission());
    }

    public function testShouldIncludeFullPermissionReturnsFalseWhenAlreadyHasFull(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::FULL]);

        $this->assertFalse($role->shouldIncludeFullPermission());
    }

    public function testShouldIncludeFullPermissionReturnsFalseForOnlyView(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::VIEW]);

        $this->assertFalse($role->shouldIncludeFullPermission());
    }

    public function testShouldIncludeFullPermissionReturnsTrueForMultiplePermissions(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::VIEW, Permission::EDIT]);

        $this->assertTrue($role->shouldIncludeFullPermission());
    }

    public function testIsSingleRole(): void
    {
        $roleWithoutPermissions = new Role('ROLE_TEST', 'Test Role', []);
        $roleWithPermissions = new Role('ROLE_TEST', 'Test Role', [Permission::VIEW]);

        $this->assertTrue($roleWithoutPermissions->isSingleRole());
        $this->assertFalse($roleWithPermissions->isSingleRole());
    }

    public function testGetHighestLevelPermissionsWithNoPermissions(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', []);

        $this->assertSame([], $role->getHighestLevelPermissions());
    }

    public function testGetHighestLevelPermissionsWithSinglePermission(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::VIEW]);

        $highestPermissions = $role->getHighestLevelPermissions();

        $this->assertCount(1, $highestPermissions);
        $this->assertContains(Permission::VIEW, $highestPermissions);
    }

    public function testGetHighestLevelPermissionsWithHierarchicalPermissions(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::VIEW, Permission::EDIT, Permission::CREATE]);

        $highestPermissions = $role->getHighestLevelPermissions();

        $this->assertCount(2, $highestPermissions);
        $this->assertContains(Permission::EDIT, $highestPermissions);
        $this->assertContains(Permission::CREATE, $highestPermissions);
        $this->assertNotContains(Permission::VIEW, $highestPermissions);
    }

    public function testGetHighestLevelPermissionsWithFullPermission(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::FULL]);

        $highestPermissions = $role->getHighestLevelPermissions();

        $this->assertCount(1, $highestPermissions);
        $this->assertContains(Permission::FULL, $highestPermissions);

        $calculatedHighest = $role->calculateHighestValidPermissions([Permission::VIEW, Permission::EDIT, Permission::CREATE]);

        $this->assertCount(2, $calculatedHighest);
        $this->assertContains(Permission::EDIT, $calculatedHighest);
        $this->assertContains(Permission::CREATE, $calculatedHighest);

        $calculatedHighest = $role->calculateHighestValidPermissions([Permission::VIEW, Permission::EDIT, Permission::CREATE, Permission::DELETE]);

        $this->assertCount(1, $calculatedHighest);
        $this->assertContains(Permission::FULL, $calculatedHighest);
    }

    public function testGetHighestLevelPermissionsWithMixedPermissions(): void
    {
        $role = new Role('ROLE_TEST', 'Test Role', [Permission::VIEW, Permission::DELETE]);

        $highestPermissions = $role->getHighestLevelPermissions();

        $this->assertCount(1, $highestPermissions);
        $this->assertContains(Permission::DELETE, $highestPermissions);
        $this->assertNotContains(Permission::VIEW, $highestPermissions);

        $calculatedHighest = $role->calculateHighestValidPermissions([Permission::VIEW, Permission::EDIT, Permission::CREATE]);

        $this->assertCount(1, $calculatedHighest);
        $this->assertContains(Permission::VIEW, $calculatedHighest);
    }
}
