<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\DataTransformer;

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Form\DataTransformer\RolesGridDataTransformer;

class RolesGridDataTransformerTest extends TestCase
{
    private RolesGridDataTransformer $transformer;

    private RolesGridDataTransformer $simpleTransformer;

    private RoleRegistryInterface&MockObject $roleRegistry;

    #[Override]
    protected function setUp(): void
    {
        $this->roleRegistry = $this->createMock(RoleRegistryInterface::class);
        $this->transformer = new RolesGridDataTransformer(
            $this->roleRegistry,
            AdminContext::class,
            ['ROLE_SUPER_ADMIN'],
            array_map(fn (Permission $permission) => $permission, Permission::cases()), // availablePermissions (full mode)
        );

        $this->simpleTransformer = new RolesGridDataTransformer(
            $this->roleRegistry,
            AdminContext::class,
            ['ROLE_SUPER_ADMIN'],
            [Permission::VIEW, Permission::FULL], // availablePermissions (simple mode)
        );
    }

    public function testTransformRoleIdentifiers(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]),
        ]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW'];

        $result = $this->transformer->transform($roleIdentifiers);

        $expected = [
            'ROLE_ORDER' => [
                'VIEW' => true,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testTransformWithFullPermission(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::FULL]),
        ]);

        $roleIdentifiers = ['ROLE_ORDER_FULL'];

        $result = $this->transformer->transform($roleIdentifiers);

        $expected = [
            'ROLE_ORDER' => [
                'FULL' => true,
                'EDIT' => true,
                'VIEW' => true,
                'CREATE' => true,
                'DELETE' => true,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testTransformWithSubordinatePermissions(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::EDIT]),
        ]);

        $roleIdentifiers = ['ROLE_ORDER_EDIT'];

        $result = $this->transformer->transform($roleIdentifiers);

        $expected = [
            'ROLE_ORDER' => [
                'EDIT' => true,
                'VIEW' => true, // subordinate permission
                'FULL' => true,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformMultidimensionalArray(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]),
        ]);

        $formData = [
            'ROLE_ORDER' => [
                'VIEW' => true,
                'EDIT' => false,
                'CREATE' => true, // This should be filtered out as role doesn't have this permission
            ],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_ORDER_VIEW'];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformWithFullPermission(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::FULL]),
        ]);

        $formData = [
            'ROLE_ORDER' => [
                'VIEW' => true,
                'EDIT' => true,
                'FULL' => true,
            ],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_ORDER_FULL'];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformExcludesExcludedRoles(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]),
        ]);

        $formData = [
            'ROLE_SUPER_ADMIN' => [
                'VIEW' => true,
            ],
            'ROLE_ORDER' => [
                'VIEW' => true,
            ],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_ORDER_VIEW'];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformWithVariousCheckboxValues(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]),
        ]);

        $formData = [
            'ROLE_ORDER' => [
                'VIEW' => true,
                'EDIT' => '1',  // String '1' should be treated as true
                'CREATE' => 1,  // Integer 1 should be treated as true (but filtered by role permissions)
                'DELETE' => false,
                'FULL' => '0',  // String '0' should be treated as false
            ],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_ORDER_EDIT'];

        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public function testReverseTransformInvalidPermission(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]),
        ]);

        $formData = [
            'ROLE_ORDER' => [
                'INVALID_PERMISSION' => true,
                'VIEW' => true,
            ],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_ORDER_VIEW'];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformWithFullPermissionNotAvailable(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]),
        ]);

        $formData = [
            'ROLE_ORDER' => [
                'VIEW' => true,
                'EDIT' => true,
                'FULL' => true, // This should be filtered out as role doesn't have FULL permission
            ],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_ORDER_EDIT'];

        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public function testTransformMultipleRoles(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]),
            new Role('ROLE_PRODUCT', 'Product Role', [Permission::VIEW, Permission::FULL]),
        ]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_PRODUCT_FULL'];

        $result = $this->transformer->transform($roleIdentifiers);

        $expected = [
            'ROLE_ORDER' => [
                'EDIT' => false,
                'VIEW' => true,
                'FULL' => false,
            ],
            'ROLE_PRODUCT' => [
                'FULL' => true,
                'EDIT' => true,
                'VIEW' => true,
                'CREATE' => true,
                'DELETE' => true,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformMultipleRoles(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]),
            new Role('ROLE_PRODUCT', 'Product Role', [Permission::VIEW, Permission::FULL]),
        ]);

        $formData = [
            'ROLE_ORDER' => [
                'VIEW' => true,
                'EDIT' => true,
            ],
            'ROLE_PRODUCT' => [
                'FULL' => true,
            ],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_ORDER_EDIT', 'ROLE_PRODUCT_FULL'];

        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public function testReverseTransformWithFalsyValues(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]),
        ]);

        $formData = [
            'ROLE_ORDER' => [
                'VIEW' => false,
                'EDIT' => 0,
                'CREATE' => '',
                'DELETE' => null,
            ],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $this->assertSame([], $result);
    }

    public function testReverseTransformWithNonArrayRoleData(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_PRODUCT', 'Product Role', [Permission::VIEW]),
        ]);

        $formData = [
            'ROLE_ORDER' => 'not_an_array',
            'ROLE_PRODUCT' => [
                'VIEW' => true,
            ],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_PRODUCT_VIEW'];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformViewOnlyRoleWithFullChecked(): void
    {
        // This test simulates the bug: ROLE_INQUIRY has only VIEW permission,
        // but when "Select All FULL" is clicked in UI, all available permissions should be checked
        $this->prepareRolesMock([
            new Role('ROLE_INQUIRY', 'Inquiry Role', [Permission::VIEW]),
        ]);

        // This would be the form data when "Select All FULL" is clicked in UI
        // and the role only has VIEW permission available
        $formData = [
            'ROLE_INQUIRY' => [
                'VIEW' => true, // This should be checked when FULL is selected for this role
            ],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_INQUIRY_VIEW'];

        $this->assertSame($expected, $result);
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Role> $roles
     */
    private function prepareRolesMock(array $roles): void
    {
        $this->roleRegistry
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn($roles);

        $this->roleRegistry
            ->method('getRole')
            ->willReturnCallback(function (string $roleIdentifier, string $context) use ($roles) {
                $roleConstant = RoleIdentifierHelper::getRoleConstantFromIdentifier($roleIdentifier);

                foreach ($roles as $role) {
                    if ($role->getConstant() === $roleConstant) {
                        return $role;
                    }
                }

                throw new InvalidArgumentException(sprintf('Role "%s" not found', $roleConstant));
            });
    }

    // Simple permissions mode tests

    public function testTransformInSimplePermissionsMode(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]),
        ]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_ORDER_EDIT'];

        $result = $this->simpleTransformer->transform($roleIdentifiers);

        $expected = [
            'ROLE_ORDER' => [
                'EDIT' => true,  // Transform doesn't filter, only reverseTransform does
                'VIEW' => true,  // Both VIEW and EDIT are shown normally
                'FULL' => true,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testTransformFullPermissionInSimpleMode(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_PRODUCT', 'Order product', [Permission::FULL]),
        ]);

        $roleIdentifiers = ['ROLE_PRODUCT_FULL'];

        $result = $this->simpleTransformer->transform($roleIdentifiers);

        $expected = [
            'ROLE_PRODUCT' => [
                'FULL' => true,
                'EDIT' => true,
                'VIEW' => true,
                'CREATE' => true,
                'DELETE' => true,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformInSimplePermissionsMode(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::FULL]),
        ]);

        $formData = [
            'ROLE_ORDER' => [
                'VIEW' => true,
                'FULL' => true,  // Role has FULL permission, so save FULL
            ],
        ];

        $result = $this->simpleTransformer->reverseTransform($formData);

        $expected = ['ROLE_ORDER_FULL'];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformViewOnlyInSimpleMode(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_INQUIRY', 'Inquiry Role', [Permission::VIEW]),
        ]);

        $formData = [
            'ROLE_INQUIRY' => [
                'VIEW' => true,
                'FULL' => false,  // This role doesn't have FULL permission
            ],
        ];

        $result = $this->simpleTransformer->reverseTransform($formData);

        $expected = ['ROLE_INQUIRY_VIEW'];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformFiltersUnavailablePermissions(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT, Permission::CREATE, Permission::DELETE]),
        ]);

        // Form data includes permissions that should be filtered out
        $formData = [
            'ROLE_ORDER' => [
                'VIEW' => true,
                'EDIT' => true,    // This should be filtered out (not in availablePermissions)
                'CREATE' => true,  // This should be filtered out (not in availablePermissions)
                'DELETE' => true,  // This should be filtered out (not in availablePermissions)
                'FULL' => false,   // This should be kept but not checked
            ],
        ];

        $result = $this->simpleTransformer->reverseTransform($formData);

        // Only VIEW should be saved since only VIEW and FULL are in availablePermissions
        $expected = ['ROLE_ORDER_VIEW'];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformFullWhenAvailable(): void
    {
        // Test that FULL permission works when it's in availablePermissions
        $this->prepareRolesMock([
            new Role('ROLE_PRODUCT', 'Product Role', [Permission::VIEW, Permission::FULL]),
        ]);

        $formData = [
            'ROLE_PRODUCT' => [
                'VIEW' => true,
                'FULL' => true,  // Should save FULL since it's available
                'EDIT' => true,  // Should be filtered out
            ],
        ];

        $result = $this->simpleTransformer->reverseTransform($formData);

        $expected = ['ROLE_PRODUCT_FULL'];

        $this->assertSame($expected, $result);
    }

    // Additional test cases for better coverage

    public function testTransformWithEmptyArray(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]),
        ]);

        $result = $this->transformer->transform([]);

        $expected = [
            'ROLE_ORDER' => [
                'VIEW' => false,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testTransformWithNonArrayInput(): void
    {
        $result = $this->transformer->transform('not an array');
        $this->assertSame([], $result);

        $result = $this->transformer->transform(null);
        $this->assertSame([], $result);

        $result = $this->transformer->transform(123);
        $this->assertSame([], $result);
    }

    public function testReverseTransformWithEmptyPermissionsArray(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]),
        ]);

        $formData = [
            'ROLE_ORDER' => [],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $this->assertSame([], $result);
    }

    public function testTransformWhenRoleIdentifierPermissionNotAvailable(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]), // Role doesn't have EDIT
        ]);

        $roleIdentifiers = ['ROLE_ORDER_EDIT']; // Trying to set EDIT which role doesn't have

        $result = $this->transformer->transform($roleIdentifiers);

        // Should initialize role but not set any permissions since role doesn't have EDIT
        $expected = [
            'ROLE_ORDER' => [
                'VIEW' => false,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testTransformWithExcludedRoleInGetRoles(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]),
            new Role('ROLE_SUPER_ADMIN', 'Super Admin', [Permission::FULL]), // This is excluded
        ]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_SUPER_ADMIN_FULL'];

        $result = $this->transformer->transform($roleIdentifiers);

        // Should only include ROLE_ORDER, not ROLE_SUPER_ADMIN
        $expected = [
            'ROLE_ORDER' => [
                'VIEW' => true,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testTransformWithRoleThatShouldIncludeFullPermission(): void
    {
        // Role with multiple permissions (VIEW, EDIT) but not FULL
        // This should trigger shouldIncludeFullPermission() to return true
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]),
        ]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_ORDER_EDIT'];

        $result = $this->transformer->transform($roleIdentifiers);

        $expected = [
            'ROLE_ORDER' => [
                'EDIT' => true,
                'VIEW' => true,
                'FULL' => true, // Should be auto-set because all permissions are checked
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformWithNonArrayInput(): void
    {
        $result = $this->transformer->reverseTransform('not an array');
        $this->assertSame([], $result);

        $result = $this->transformer->reverseTransform(null);
        $this->assertSame([], $result);
    }

    public function testReverseTransformFullPermissionWhenRoleDoesntHaveItButAllPermissionsChecked(): void
    {
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT, Permission::CREATE, Permission::DELETE]),
        ]);

        $formData = [
            'ROLE_ORDER' => [
                'EDIT' => true,
                'VIEW' => true,
                'CREATE' => true,
                'DELETE' => true,
                'FULL' => true, // Checked but role doesn't have FULL permission
            ],
        ];

        $result = $this->transformer->reverseTransform($formData);

        // Should return all individual permissions since FULL is not available
        $expected = ['ROLE_ORDER_EDIT', 'ROLE_ORDER_CREATE', 'ROLE_ORDER_DELETE'];

        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public function testTransformWithRoleThatShouldNotIncludeFullPermission(): void
    {
        // Role with only VIEW permission should NOT include FULL (shouldIncludeFullPermission() returns false)
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]),
        ]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW'];

        $result = $this->transformer->transform($roleIdentifiers);

        $expected = [
            'ROLE_ORDER' => [
                'VIEW' => true,
                // FULL should not be included because role only has VIEW
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testTransformWithRoleAlreadyHavingFullPermission(): void
    {
        // Role that already has FULL permission should NOT include FULL again (shouldIncludeFullPermission() returns false)
        $this->prepareRolesMock([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT, Permission::CREATE, Permission::DELETE, Permission::FULL]),
        ]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_ORDER_EDIT'];

        $result = $this->transformer->transform($roleIdentifiers);

        $expected = [
            'ROLE_ORDER' => [
                'FULL' => false, // Should remain false, not auto-set because role already has FULL
                'EDIT' => true,
                'VIEW' => true,
                'CREATE' => false,
                'DELETE' => false,
            ],
        ];

        $this->assertSame($expected, $result);
    }
}
