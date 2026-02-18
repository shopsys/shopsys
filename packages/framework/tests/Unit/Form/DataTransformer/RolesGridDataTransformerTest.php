<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\DataTransformer;

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider;
use Shopsys\FrameworkBundle\Form\DataTransformer\RolesGridDataTransformer;

class RolesGridDataTransformerTest extends TestCase
{
    private RolesGridDataTransformer $transformer;

    private RolesGridDataTransformer $simpleTransformer;

    private Stub|RoleRegistryInterface $roleRegistryStub;

    protected const string ORDER_SECTION = 'orders_customers';
    protected const string PRODUCT_SECTION = 'products_catalog';

    #[Override]
    protected function setUp(): void
    {
        $this->roleRegistryStub = $this->createStub(RoleRegistryInterface::class);
        $this->transformer = new RolesGridDataTransformer(
            $this->roleRegistryStub,
            AdminContext::class,
            ['ROLE_SUPER_ADMIN'],
            true, // shouldGroupBySections
        );

        $this->simpleTransformer = new RolesGridDataTransformer(
            $this->roleRegistryStub,
            AdminContext::class,
            ['ROLE_SUPER_ADMIN'],
            false, // shouldGroupBySections - use virtual section when false
        );
    }

    public function testTransformRoleIdentifiers(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]);
        $this->prepareRolesStub([$role]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW'];

        $result = $this->transformer->transform($roleIdentifiers);

        $this->assertArrayHasKey(AbstractRoleSectionProvider::OTHER, $result);
        $this->assertSame(['ROLE_ORDER_VIEW'], $result[AbstractRoleSectionProvider::OTHER]);
    }

    public function testTransformWithFullPermission(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::FULL]);
        $this->prepareRolesStub([$role]);

        $roleIdentifiers = ['ROLE_ORDER_FULL'];

        $result = $this->transformer->transform($roleIdentifiers);

        $this->assertArrayHasKey(AbstractRoleSectionProvider::OTHER, $result);
        $this->assertSame(['ROLE_ORDER_FULL'], $result[AbstractRoleSectionProvider::OTHER]);
    }

    public function testTransformWithVirtualSection(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::EDIT]);
        $this->prepareRolesStub([$role]);

        $roleIdentifiers = ['ROLE_ORDER_EDIT', 'ROLE_ORDER_VIEW'];

        $result = $this->simpleTransformer->transform($roleIdentifiers);

        // Should use virtual section 'all_roles'
        $this->assertArrayHasKey(AbstractRoleSectionProvider::OTHER, $result);
        $this->assertEqualsCanonicalizing(['ROLE_ORDER_EDIT', 'ROLE_ORDER_VIEW'], $result[AbstractRoleSectionProvider::OTHER]);
    }

    public function testReverseTransformSectionData(): void
    {
        $formData = [
            AbstractRoleSectionProvider::OTHER => ['ROLE_ORDER_VIEW', 'ROLE_ORDER_EDIT'],
            self::PRODUCT_SECTION => ['ROLE_PRODUCT_VIEW'],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_ORDER_VIEW', 'ROLE_ORDER_EDIT', 'ROLE_PRODUCT_VIEW'];

        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public function testReverseTransformWithVirtualSection(): void
    {
        $formData = [
            AbstractRoleSectionProvider::OTHER => ['ROLE_ORDER_VIEW', 'ROLE_ORDER_FULL'],
        ];

        $result = $this->simpleTransformer->reverseTransform($formData);

        $expected = ['ROLE_ORDER_VIEW', 'ROLE_ORDER_FULL'];

        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public function testTransformExcludesExcludedRoles(): void
    {
        $this->prepareRolesStub([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]),
            new Role('ROLE_SUPER_ADMIN', 'Super Admin Role', [Permission::FULL]),
        ]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_SUPER_ADMIN_FULL'];

        $result = $this->transformer->transform($roleIdentifiers);

        // Should only include ROLE_ORDER, not ROLE_SUPER_ADMIN
        $this->assertArrayHasKey(AbstractRoleSectionProvider::OTHER, $result);
        $this->assertSame(['ROLE_ORDER_VIEW'], $result[AbstractRoleSectionProvider::OTHER]);
    }

    public function testReverseTransformWithEmptySection(): void
    {
        $formData = [
            AbstractRoleSectionProvider::OTHER => [],
            self::PRODUCT_SECTION => ['ROLE_PRODUCT_VIEW'],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_PRODUCT_VIEW'];

        $this->assertSame($expected, $result);
    }

    public function testTransformWithInvalidIdentifier(): void
    {
        $this->prepareRolesStub([
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]),
        ]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW', 'INVALID_IDENTIFIER', 'ROLE_NONEXISTENT_VIEW'];

        $result = $this->transformer->transform($roleIdentifiers);

        $expected = [
            AbstractRoleSectionProvider::OTHER => ['ROLE_ORDER_VIEW'],
        ];

        $this->assertSame($expected, $result);
    }

    public function testTransformWithNonArrayInput(): void
    {
        $result = $this->transformer->transform(null);
        $this->assertSame([], $result);

        $result = $this->transformer->transform('not an array');
        $this->assertSame([], $result);
    }

    public function testTransformMultipleRolesWithSections(): void
    {
        $orderRole = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]);
        $orderRole->setRoleSection(self::ORDER_SECTION);

        $productRole = new Role('ROLE_PRODUCT', 'Product Role', [Permission::FULL]);
        $productRole->setRoleSection(self::PRODUCT_SECTION);

        $this->prepareRolesStub([$orderRole, $productRole]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_PRODUCT_FULL'];

        $result = $this->transformer->transform($roleIdentifiers);

        // Should group by different sections
        $this->assertArrayHasKey(self::ORDER_SECTION, $result);
        $this->assertArrayHasKey(self::PRODUCT_SECTION, $result);
        $this->assertSame(['ROLE_ORDER_VIEW'], $result[self::ORDER_SECTION]);
        $this->assertSame(['ROLE_PRODUCT_FULL'], $result[self::PRODUCT_SECTION]);
    }

    public function testReverseTransformWithNonArrayInput(): void
    {
        $result = $this->transformer->reverseTransform(null);
        $this->assertSame([], $result);

        $result = $this->transformer->reverseTransform('not an array');
        $this->assertSame([], $result);
    }

    public function testReverseTransformWithNonArraySectionData(): void
    {
        $formData = [
            AbstractRoleSectionProvider::OTHER => 'not an array',
            self::PRODUCT_SECTION => ['ROLE_PRODUCT_VIEW'],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_PRODUCT_VIEW'];
        $this->assertSame($expected, $result);
    }

    public function testTransformWithNonStringIdentifiers(): void
    {
        $role = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]);
        $this->prepareRolesStub([$role]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW', 123, null, false, ['array']];

        $result = $this->transformer->transform($roleIdentifiers);

        $this->assertArrayHasKey(AbstractRoleSectionProvider::OTHER, $result);
        $this->assertSame(['ROLE_ORDER_VIEW'], $result[AbstractRoleSectionProvider::OTHER]);
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Role> $roles
     */
    private function prepareRolesStub(array $roles): void
    {
        $this->roleRegistryStub
            ->method('getRoles')
            ->willReturnMap([
                [AdminContext::class, $roles],
            ]);

        $this->roleRegistryStub
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

    // Virtual section tests (used when sections are disabled)

    public function testTransformWithVirtualSectionGroupsAllRoles(): void
    {
        $orderRole = new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW]);
        $productRole = new Role('ROLE_PRODUCT', 'Product Role', [Permission::FULL]);
        $this->prepareRolesStub([$orderRole, $productRole]);

        $roleIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_PRODUCT_FULL'];

        $result = $this->simpleTransformer->transform($roleIdentifiers);

        // All roles should be in 'all_roles' virtual section
        $this->assertArrayHasKey(AbstractRoleSectionProvider::OTHER, $result);
        $this->assertEqualsCanonicalizing(['ROLE_ORDER_VIEW', 'ROLE_PRODUCT_FULL'], $result[AbstractRoleSectionProvider::OTHER]);
    }

    // Edge cases

    public function testTransformWithEmptyArray(): void
    {
        $result = $this->transformer->transform([]);
        $this->assertSame([], $result);
    }

    public function testReverseTransformWithEmptyArray(): void
    {
        $result = $this->transformer->reverseTransform([]);
        $this->assertSame([], $result);
    }

    public function testReverseTransformWithMixedSectionData(): void
    {
        $formData = [
            AbstractRoleSectionProvider::OTHER => ['ROLE_ORDER_VIEW', 'ROLE_PRODUCT_FULL'],
            self::PRODUCT_SECTION => ['ROLE_INQUIRY_VIEW'],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $expected = ['ROLE_ORDER_VIEW', 'ROLE_PRODUCT_FULL', 'ROLE_INQUIRY_VIEW'];
        $this->assertEqualsCanonicalizing($expected, $result);
    }
}
