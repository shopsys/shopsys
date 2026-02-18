<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Security\AccessControl;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessChecker;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AccessCheckerTest extends TestCase
{
    private const string ROLE_CONSTANT = 'ROLE';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Permission[] $availablePermissions
     */
    #[DataProvider('hasPermissionDataProvider')]
    public function testHasPermissionPassesCorrectRoleIdentifierToAuthorizationChecker(
        array $availablePermissions,
        Permission $permission,
        string $expectedRoleIdentifier,
    ): void {
        $role = $this->createRole($availablePermissions);
        $roleConstant = $role->getConstant();

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with($expectedRoleIdentifier);

        $roleRegistry = $this->createMock(RoleRegistryInterface::class);
        $roleRegistry->expects($this->any())->method('getRole')
            ->with($roleConstant, AdminContext::class)
            ->willReturn($role);

        $routeAccessChecker = $this->createStub(RouteAccessCheckerInterface::class);

        $accessChecker = new AccessChecker($authorizationChecker, $roleRegistry, $routeAccessChecker);

        $accessChecker->hasPermission($roleConstant, $permission);
    }

    /**
     * @return iterable<string, array{availablePermissions: array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission>, permission: \Shopsys\FrameworkBundle\Component\Security\Role\Permission, expectedRoleIdentifier: string}>
     */
    public static function hasPermissionDataProvider(): iterable
    {
        yield 'single role passes role constant directly' => [
            'availablePermissions' => [],
            'permission' => Permission::VIEW,
            'expectedRoleIdentifier' => self::ROLE_CONSTANT,
        ];

        yield 'permission role passes role constant with permission' => [
            'availablePermissions' => [Permission::EDIT],
            'permission' => Permission::EDIT,
            'expectedRoleIdentifier' => RoleIdentifierHelper::getIdentifierWithPermission(self::ROLE_CONSTANT, Permission::EDIT),
        ];
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $availablePermissions
     */
    private function createRole(array $availablePermissions = []): Role
    {
        return new Role(self::ROLE_CONSTANT, 'Role name', $availablePermissions);
    }
}
