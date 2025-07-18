<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Security\Role;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use ValueError;

class PermissionTest extends TestCase
{
    public function testGetSubordinatePermissionsDirectOnly(): void
    {
        $fullSubordinates = Permission::FULL->getSubordinatePermissions(true);
        $this->assertCount(3, $fullSubordinates);
        $this->assertContains(Permission::EDIT, $fullSubordinates);
        $this->assertContains(Permission::CREATE, $fullSubordinates);
        $this->assertContains(Permission::DELETE, $fullSubordinates);

        $editSubordinates = Permission::EDIT->getSubordinatePermissions(true);
        $this->assertCount(1, $editSubordinates);
        $this->assertContains(Permission::VIEW, $editSubordinates);

        $viewSubordinates = Permission::VIEW->getSubordinatePermissions(true);
        $this->assertCount(0, $viewSubordinates);
    }

    public function testGetSubordinatePermissionsRecursive(): void
    {
        $fullSubordinates = Permission::FULL->getSubordinatePermissions();
        $this->assertCount(4, $fullSubordinates);
        $this->assertContains(Permission::VIEW, $fullSubordinates);
        $this->assertContains(Permission::EDIT, $fullSubordinates);
        $this->assertContains(Permission::CREATE, $fullSubordinates);
        $this->assertContains(Permission::DELETE, $fullSubordinates);
    }

    public function testIncludes(): void
    {
        $this->assertTrue(Permission::FULL->includes(Permission::FULL));
        $this->assertTrue(Permission::FULL->includes(Permission::VIEW));
        $this->assertTrue(Permission::FULL->includes(Permission::EDIT));
        $this->assertTrue(Permission::FULL->includes(Permission::CREATE));
        $this->assertTrue(Permission::FULL->includes(Permission::DELETE));

        $this->assertTrue(Permission::EDIT->includes(Permission::EDIT));
        $this->assertTrue(Permission::EDIT->includes(Permission::VIEW));
        $this->assertFalse(Permission::EDIT->includes(Permission::CREATE));
        $this->assertFalse(Permission::EDIT->includes(Permission::DELETE));
        $this->assertFalse(Permission::EDIT->includes(Permission::FULL));

        $this->assertTrue(Permission::VIEW->includes(Permission::VIEW));
        $this->assertFalse(Permission::VIEW->includes(Permission::EDIT));
        $this->assertFalse(Permission::VIEW->includes(Permission::CREATE));
        $this->assertFalse(Permission::VIEW->includes(Permission::DELETE));
        $this->assertFalse(Permission::VIEW->includes(Permission::FULL));
    }

    public function testExpand(): void
    {
        $expanded = Permission::expand([Permission::EDIT, Permission::CREATE]);
        $this->assertCount(3, $expanded);
        $this->assertContains(Permission::EDIT, $expanded);
        $this->assertContains(Permission::CREATE, $expanded);
        $this->assertContains(Permission::VIEW, $expanded);

        $fullExpanded = Permission::expand([Permission::FULL]);
        $this->assertCount(5, $fullExpanded);
        $this->assertContains(Permission::FULL, $fullExpanded);
        $this->assertContains(Permission::VIEW, $fullExpanded);
        $this->assertContains(Permission::EDIT, $fullExpanded);
        $this->assertContains(Permission::CREATE, $fullExpanded);
        $this->assertContains(Permission::DELETE, $fullExpanded);
    }

    public function testGetHighestLevelPermissions(): void
    {
        $highest = Permission::getHighestLevelPermissions([Permission::VIEW, Permission::EDIT]);
        $this->assertCount(1, $highest);
        $this->assertSame(Permission::EDIT, $highest[0]);

        $highest = Permission::getHighestLevelPermissions([Permission::CREATE, Permission::DELETE, Permission::VIEW]);
        $this->assertCount(2, $highest);
        $this->assertContains(Permission::CREATE, $highest);
        $this->assertContains(Permission::DELETE, $highest);
        $this->assertNotContains(Permission::VIEW, $highest);

        $highest = Permission::getHighestLevelPermissions([Permission::FULL]);
        $this->assertCount(1, $highest);
        $this->assertSame(Permission::FULL, $highest[0]);
    }

    public function testFromValues(): void
    {
        $permissions = Permission::fromValues('VIEW', 'EDIT', 'CREATE');
        $this->assertCount(3, $permissions);
        $this->assertContains(Permission::VIEW, $permissions);
        $this->assertContains(Permission::EDIT, $permissions);
        $this->assertContains(Permission::CREATE, $permissions);

        $this->expectException(ValueError::class);
        Permission::fromValues('INVALID_PERMISSION');
    }

    public function testToValues(): void
    {
        $values = Permission::toValues(Permission::VIEW, Permission::EDIT);
        $this->assertSame(['VIEW', 'EDIT'], $values);

        $allValues = Permission::toValues(...Permission::cases());
        $this->assertContains('VIEW', $allValues);
        $this->assertContains('EDIT', $allValues);
        $this->assertContains('CREATE', $allValues);
        $this->assertContains('DELETE', $allValues);
        $this->assertContains('FULL', $allValues);
    }

    public function testExpandWithDuplicates(): void
    {
        $expanded = Permission::expand([Permission::EDIT, Permission::VIEW, Permission::EDIT]);
        $this->assertContains(Permission::EDIT, $expanded);
        $this->assertContains(Permission::VIEW, $expanded);
        $viewCount = 0;

        foreach ($expanded as $permission) {
            if ($permission === Permission::VIEW) {
                $viewCount++;
            }
        }
        $this->assertSame(1, $viewCount);
    }

    public function testGetHighestLevelPermissionsWithEmpty(): void
    {
        // Test edge case: empty input
        $highest = Permission::getHighestLevelPermissions([]);
        $this->assertSame([], $highest);
    }

    public function testExpandWithEmpty(): void
    {
        $expanded = Permission::expand([]);
        $this->assertSame([], $expanded);
    }

    public function testPermissionHierarchyPreventsBypass(): void
    {
        $this->assertFalse(Permission::VIEW->includes(Permission::CREATE));
        $this->assertFalse(Permission::VIEW->includes(Permission::EDIT));
        $this->assertFalse(Permission::VIEW->includes(Permission::DELETE));
        $this->assertFalse(Permission::VIEW->includes(Permission::FULL));

        $this->assertFalse(Permission::CREATE->includes(Permission::FULL));
        $this->assertFalse(Permission::EDIT->includes(Permission::FULL));
        $this->assertFalse(Permission::DELETE->includes(Permission::FULL));
    }

    public function testPermissionEscalationPrevention(): void
    {
        $this->assertCount(0, Permission::VIEW->getSubordinatePermissions());
        $this->assertSame([Permission::VIEW], Permission::CREATE->getSubordinatePermissions());
        $this->assertSame([Permission::VIEW], Permission::EDIT->getSubordinatePermissions());
        $this->assertSame([Permission::VIEW], Permission::DELETE->getSubordinatePermissions());
    }

    public function testPermissionBoundaryIntegrity(): void
    {
        foreach (Permission::cases() as $permission) {
            $subordinates = $permission->getSubordinatePermissions();

            $this->assertNotContains($permission, $subordinates);

            foreach ($subordinates as $subordinate) {
                $this->assertTrue($permission->includes($subordinate));
                $this->assertFalse($subordinate->includes($permission));
            }
        }
    }

    public function testExpansionDoesNotAllowPrivilegeEscalation(): void
    {
        $viewOnly = Permission::expand([Permission::VIEW]);
        $this->assertCount(1, $viewOnly);
        $this->assertSame(Permission::VIEW, $viewOnly[0]);

        $allSubordinates = [Permission::VIEW, Permission::CREATE, Permission::EDIT, Permission::DELETE];
        $expanded = Permission::expand($allSubordinates);
        $this->assertNotContains(Permission::FULL, $expanded);

        $explicitFull = Permission::expand([Permission::FULL]);
        $this->assertContains(Permission::FULL, $explicitFull);
        $this->assertContains(Permission::VIEW, $explicitFull);
        $this->assertContains(Permission::CREATE, $explicitFull);
        $this->assertContains(Permission::EDIT, $explicitFull);
        $this->assertContains(Permission::DELETE, $explicitFull);
    }

    public function testPermissionHierarchyKeyInvariants(): void
    {
        $testSets = [
            [Permission::EDIT, Permission::VIEW],
            [Permission::FULL],
            [Permission::CREATE, Permission::DELETE],
        ];

        foreach ($testSets as $permissions) {
            $expanded = Permission::expand($permissions);
            $highest = Permission::getHighestLevelPermissions($permissions);

            foreach ($permissions as $permission) {
                $this->assertContains($permission, $expanded);
            }

            foreach ($highest as $highPermission) {
                $this->assertContains($highPermission, $permissions);
            }
        }
    }

    public function testPermissionExpansionIdempotency(): void
    {
        foreach (Permission::cases() as $permission) {
            $expanded1 = Permission::expand([$permission]);
            $expanded2 = Permission::expand($expanded1);
            $this->assertEqualsCanonicalizing($expanded1, $expanded2);
        }
    }

    public function testPermissionOperationConsistency(): void
    {
        foreach (Permission::cases() as $permission) {
            $subordinates = $permission->getSubordinatePermissions();
            $expanded = Permission::expand([$permission]);
            $expectedExpanded = array_merge([$permission], $subordinates);

            $this->assertEqualsCanonicalizing($expectedExpanded, $expanded);
        }
    }
}
