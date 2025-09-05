<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Form\DataTransformer\RoleRowDataTransformer;

class RoleRowDataTransformerTest extends TestCase
{
    public function testTransformWithSingleRole(): void
    {
        $role = new Role('ROLE_INQUIRY', 'Inquiry Role', []); // No permissions = single role
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::FULL]);

        $roleIdentifiers = ['ROLE_INQUIRY'];

        $result = $transformer->transform($roleIdentifiers);

        // Single role returns array with role constant as key
        $expected = ['ROLE_INQUIRY' => true];
        $this->assertSame($expected, $result);
    }

    public function testTransformWithSingleRoleNotInIdentifiers(): void
    {
        $role = new Role('ROLE_INQUIRY', 'Inquiry Role', []); // No permissions = single role
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::FULL]);

        $roleIdentifiers = ['ROLE_OTHER'];

        $result = $transformer->transform($roleIdentifiers);

        // Single role not in identifiers returns false for the role constant
        $expected = ['ROLE_INQUIRY' => false];
        $this->assertSame($expected, $result);
    }

    public function testTransformWithMultiplePermissions(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT, Permission::DELETE]);
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::EDIT, Permission::DELETE, Permission::FULL]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_ORDER_DELETE'];

        $result = $transformer->transform($roleIdentifiers);

        // Multi-permission role returns array with permission flags (order depends on role's available permissions)
        $this->assertArrayHasKey('VIEW', $result);
        $this->assertArrayHasKey('EDIT', $result);
        $this->assertArrayHasKey('DELETE', $result);
        $this->assertTrue($result['VIEW']);
        $this->assertFalse($result['EDIT']);
        $this->assertTrue($result['DELETE']);
    }

    public function testTransformWithFullPermissionFromSubordinates(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT, Permission::DELETE]);
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::EDIT, Permission::DELETE, Permission::FULL]);

        // All individual permissions are present
        $roleIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_ORDER_EDIT', 'ROLE_ORDER_DELETE'];

        $result = $transformer->transform($roleIdentifiers);

        // Should auto-set FULL when all permissions are checked
        $this->assertTrue($result['VIEW']);
        $this->assertTrue($result['EDIT']);
        $this->assertTrue($result['DELETE']);
        $this->assertTrue($result['FULL']);
    }

    public function testTransformWithRoleHavingFullPermission(): void
    {
        $role = new Role('ROLE_PRODUCT', 'Product Role', [Permission::VIEW, Permission::EDIT, Permission::FULL]);
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::EDIT, Permission::FULL]);

        $roleIdentifiers = ['ROLE_PRODUCT_FULL'];

        $result = $transformer->transform($roleIdentifiers);

        // FULL should set its subordinates
        $this->assertTrue($result['FULL']);
        $this->assertTrue($result['VIEW']);  // Subordinate of FULL
        $this->assertTrue($result['EDIT']);  // Subordinate of FULL
    }

    public function testTransformWithEmptyIdentifiers(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]);
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::EDIT, Permission::FULL]);

        $result = $transformer->transform([]);

        // All permissions should be false
        $this->assertFalse($result['VIEW']);
        $this->assertFalse($result['EDIT']);
    }

    public function testTransformWithNonArrayInput(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', []); // No permissions = single role
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW]);

        // Single role
        $result = $transformer->transform(null);
        $this->assertSame([], $result);

        $result = $transformer->transform('not an array');
        $this->assertSame([], $result);
    }

    public function testReverseTransformWithSingleRole(): void
    {
        $role = new Role('ROLE_INQUIRY', 'Inquiry Role', []); // No permissions = single role
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::FULL]);

        // Single role checked (form data has role constant as key)
        $result = $transformer->reverseTransform(['ROLE_INQUIRY' => true]);
        $this->assertSame(['ROLE_INQUIRY'], $result);

        // Single role unchecked
        $result = $transformer->reverseTransform(['ROLE_INQUIRY' => false]);
        $this->assertSame([], $result);
    }

    public function testReverseTransformWithMultiplePermissions(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT, Permission::DELETE]);
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::EDIT, Permission::DELETE, Permission::FULL]);

        $formData = [
            'VIEW' => true,
            'EDIT' => false,
            'DELETE' => true,
            'FULL' => false,
        ];

        $result = $transformer->reverseTransform($formData);

        // Should return highest valid permissions (DELETE includes VIEW, so only DELETE is returned)
        $this->assertContains('ROLE_ORDER_DELETE', $result);
        $this->assertNotContains('ROLE_ORDER_VIEW', $result); // Included in DELETE
        $this->assertNotContains('ROLE_ORDER_EDIT', $result);
    }

    public function testReverseTransformWithFullPermissionPriority(): void
    {
        $role = new Role('ROLE_PRODUCT', 'Product Role', [Permission::VIEW, Permission::EDIT, Permission::FULL]);
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::EDIT, Permission::FULL]);

        $formData = [
            'VIEW' => true,
            'EDIT' => true,
            'FULL' => true, // FULL has priority
        ];

        $result = $transformer->reverseTransform($formData);

        // Should only return FULL, not individual permissions
        $this->assertSame(['ROLE_PRODUCT_FULL'], $result);
    }

    public function testReverseTransformWithRoleNotHavingFullPermission(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT, Permission::DELETE]);
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::EDIT, Permission::DELETE, Permission::FULL]);

        $formData = [
            'VIEW' => true,
            'EDIT' => true,
            'DELETE' => true,
            'FULL' => true, // Checked but role doesn't have FULL permission
        ];

        $result = $transformer->reverseTransform($formData);

        // Should return highest valid permissions (role doesn't have FULL, so return individuals)
        // Since all permissions are checked, only the highest ones are returned
        $this->assertContains('ROLE_ORDER_EDIT', $result);
        $this->assertContains('ROLE_ORDER_DELETE', $result);
        $this->assertNotContains('ROLE_ORDER_VIEW', $result); // Included in both EDIT and DELETE
    }

    public function testReverseTransformFiltersUnavailablePermissions(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]);
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::FULL]); // Only VIEW and FULL available

        $formData = [
            'VIEW' => true,
            'EDIT' => true,   // Role has it but it's not in availablePermissions
            'DELETE' => true, // Role doesn't have it
            'FULL' => false,
        ];

        $result = $transformer->reverseTransform($formData);

        // Should only return VIEW (available and checked)
        $this->assertContains('ROLE_ORDER_VIEW', $result);
        $this->assertNotContains('ROLE_ORDER_EDIT', $result);
    }

    public function testReverseTransformWithVariousCheckboxValues(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]);
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::EDIT]);

        $formData = [
            'VIEW' => '1',    // String '1' should be treated as true
            'EDIT' => 0,      // Integer 0 should be treated as false
        ];

        $result = $transformer->reverseTransform($formData);

        $this->assertContains('ROLE_ORDER_VIEW', $result);
        $this->assertNotContains('ROLE_ORDER_EDIT', $result);
    }

    public function testReverseTransformWithNonArrayInput(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]);
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::EDIT]);

        $result = $transformer->reverseTransform(null);
        $this->assertSame([], $result);

        $result = $transformer->reverseTransform('not an array');
        $this->assertSame([], $result);
    }

    public function testTransformReverseTransformRoundTrip(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT, Permission::DELETE]);
        $transformer = new RoleRowDataTransformer($role, [Permission::VIEW, Permission::EDIT, Permission::DELETE]);

        $originalIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_ORDER_DELETE'];

        // Transform to form data
        $formData = $transformer->transform($originalIdentifiers);

        // Then reverse transform back
        $result = $transformer->reverseTransform($formData);

        // Should get back equivalent highest permissions (DELETE includes VIEW, so only DELETE is returned)
        $this->assertContains('ROLE_ORDER_DELETE', $result);
        $this->assertNotContains('ROLE_ORDER_VIEW', $result); // Included in DELETE
    }

    public function testSingleRoleDetection(): void
    {
        // Single role (no permissions)
        $singleRole = new Role('ROLE_INQUIRY', 'Inquiry Role', []); // No permissions = single role
        $singleTransformer = new RoleRowDataTransformer($singleRole, [Permission::VIEW, Permission::FULL]);

        $result = $singleTransformer->transform(['ROLE_INQUIRY']);
        $this->assertArrayHasKey('ROLE_INQUIRY', $result); // Returns array with role constant
        $this->assertTrue($result['ROLE_INQUIRY']);

        // Multiple permission role
        $multiRole = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]);
        $multiTransformer = new RoleRowDataTransformer($multiRole, [Permission::VIEW, Permission::EDIT]);

        $result = $multiTransformer->transform(['ROLE_ORDER_VIEW']);
        $this->assertIsArray($result); // Returns array for multi-permission role
        $this->assertArrayHasKey('VIEW', $result);
        $this->assertArrayHasKey('EDIT', $result);
    }
}
