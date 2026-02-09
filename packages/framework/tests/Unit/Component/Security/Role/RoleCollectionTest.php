<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Security\Role;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Security\Role\Exception\RoleCannotBeOverwrittenException;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection;

class RoleCollectionTest extends TestCase
{
    public function testAddRole(): void
    {
        $collection = new RoleCollection();
        $role = new Role('ROLE_TEST', 'Test Role');

        $collection->add($role);

        $this->assertTrue($collection->has('ROLE_TEST'));
        $this->assertSame($role, $collection->get('ROLE_TEST'));
    }

    public function testAddRoleWithOverwrite(): void
    {
        $collection = new RoleCollection();
        $role = new Role('ROLE_TEST', 'Test Role');

        $collection->add($role);

        $this->assertTrue($collection->get('ROLE_TEST')->isOverwritable());
    }

    public function testAddDuplicateRoleThrowsException(): void
    {
        $collection = new RoleCollection();
        $role1 = new Role('ROLE_TEST', 'Test Role 1');
        $role2 = new Role('ROLE_TEST', 'Test Role 2');

        $collection->add($role1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Role with constant "ROLE_TEST" already exists. Use edit() method to modify existing roles.');

        $collection->add($role2);
    }

    public function testEditRole(): void
    {
        $collection = new RoleCollection();
        $role = new Role('ROLE_TEST', 'Test Role');
        $collection->add($role);

        $collection->edit('ROLE_TEST', function (Role $role): void {
            $role->setName('Updated Role');
            $role->setAvailablePermissions([Permission::EDIT]);
        });

        $editedRole = $collection->get('ROLE_TEST');
        $this->assertSame('Updated Role', $editedRole->getName());
        $this->assertTrue($editedRole->hasPermission(Permission::EDIT));
    }

    public function testEditNonExistentRoleThrowsException(): void
    {
        $collection = new RoleCollection();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Role with constant "ROLE_TEST" does not exist.');

        $collection->edit('ROLE_TEST', function (Role $role): void {
            $role->setName('Updated Role');
        });
    }

    public function testEditProtectedRoleThrowsException(): void
    {
        $collection = new RoleCollection();
        $role = new Role('ROLE_TEST', 'Test Role', allowOverwrite: false);
        $collection->add($role);

        $this->expectException(RoleCannotBeOverwrittenException::class);

        $collection->edit('ROLE_TEST', function (Role $role): void {
            $role->setName('Updated Role');
        });
    }

    public function testGetNonExistentRoleThrowsException(): void
    {
        $collection = new RoleCollection();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Role with constant "ROLE_TEST" does not exist.');

        $collection->get('ROLE_TEST');
    }
}
