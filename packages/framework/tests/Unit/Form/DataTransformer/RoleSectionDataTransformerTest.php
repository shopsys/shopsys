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
use Shopsys\FrameworkBundle\Form\DataTransformer\RoleSectionDataTransformer;

class RoleSectionDataTransformerTest extends TestCase
{
    private RoleSectionDataTransformer $transformer;

    private Stub|RoleRegistryInterface $roleRegistryStub;

    /**
     * @var array<\Shopsys\FrameworkBundle\Component\Security\Role\Role>
     */
    private array $sectionRoles;

    #[Override]
    protected function setUp(): void
    {
        $this->roleRegistryStub = $this->createStub(RoleRegistryInterface::class);

        // Create test roles for the section
        $this->sectionRoles = [
            new Role('ROLE_ORDER', 'Order Role', [Permission::VIEW, Permission::EDIT]),
            new Role('ROLE_CUSTOMER', 'Customer Role', [Permission::VIEW, Permission::FULL]),
        ];

        $this->transformer = new RoleSectionDataTransformer(
            $this->sectionRoles,
            $this->roleRegistryStub,
            AdminContext::class,
        );
    }

    public function testTransformFiltersIdentifiersForSectionRoles(): void
    {
        $this->prepareRolesStub();

        // Input contains identifiers for roles in and outside the section
        $roleIdentifiers = [
            'ROLE_ORDER_VIEW',
            'ROLE_ORDER_EDIT',
            'ROLE_CUSTOMER_VIEW',
            'ROLE_PRODUCT_VIEW', // Not in section
            'ROLE_CATEGORY_EDIT', // Not in section
        ];

        $result = $this->transformer->transform($roleIdentifiers);

        // Should only include identifiers for roles in this section
        $expected = [
            'ROLE_ORDER' => ['ROLE_ORDER_VIEW', 'ROLE_ORDER_EDIT'],
            'ROLE_CUSTOMER' => ['ROLE_CUSTOMER_VIEW'],
        ];

        $this->assertSame($expected, $result);
    }

    public function testTransformWithEmptyArray(): void
    {
        $result = $this->transformer->transform([]);

        // Should return empty structure for section roles
        $expected = [
            'ROLE_ORDER' => [],
            'ROLE_CUSTOMER' => [],
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

    public function testTransformIgnoresInvalidIdentifiers(): void
    {
        $this->prepareRolesStub();

        $roleIdentifiers = [
            'ROLE_ORDER_VIEW',
            'INVALID_FORMAT', // No underscore
            'ROLE_ORDER_INVALID_PERMISSION', // Invalid permission
            123, // Not a string
            null,
            'ROLE_CUSTOMER_FULL',
        ];

        $result = $this->transformer->transform($roleIdentifiers);

        // Should only include valid identifiers
        $expected = [
            'ROLE_ORDER' => ['ROLE_ORDER_VIEW'],
            'ROLE_CUSTOMER' => ['ROLE_CUSTOMER_FULL'],
        ];

        $this->assertSame($expected, $result);
    }

    public function testReverseTransformCombinesRoleData(): void
    {
        $this->prepareRolesStub();

        $formData = [
            'ROLE_ORDER' => ['ROLE_ORDER_VIEW', 'ROLE_ORDER_EDIT'],
            'ROLE_CUSTOMER' => ['ROLE_CUSTOMER_FULL'],
        ];

        $result = $this->transformer->reverseTransform($formData);

        // Should combine all identifiers
        $expected = ['ROLE_ORDER_VIEW', 'ROLE_ORDER_EDIT', 'ROLE_CUSTOMER_FULL'];
        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public function testReverseTransformWithEmptyData(): void
    {
        $formData = [
            'ROLE_ORDER' => [],
            'ROLE_CUSTOMER' => [],
        ];

        $result = $this->transformer->reverseTransform($formData);

        $this->assertSame([], $result);
    }

    public function testReverseTransformWithNonArrayInput(): void
    {
        $result = $this->transformer->reverseTransform(null);
        $this->assertSame([], $result);

        $result = $this->transformer->reverseTransform('not an array');
        $this->assertSame([], $result);
    }

    public function testReverseTransformIgnoresNonArrayRoleData(): void
    {
        $formData = [
            'ROLE_ORDER' => 'not an array',
            'ROLE_CUSTOMER' => ['ROLE_CUSTOMER_VIEW'],
            'ROLE_UNKNOWN' => ['ROLE_UNKNOWN_VIEW'], // This will be included since reverseTransform doesn't filter by section
        ];

        $result = $this->transformer->reverseTransform($formData);

        // Should only include valid array data (but doesn't filter by section in reverseTransform)
        $expected = ['ROLE_CUSTOMER_VIEW', 'ROLE_UNKNOWN_VIEW'];
        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public function testTransformReverseTransformRoundTrip(): void
    {
        $this->prepareRolesStub();

        $originalIdentifiers = ['ROLE_ORDER_VIEW', 'ROLE_ORDER_EDIT', 'ROLE_CUSTOMER_FULL'];

        // Transform to form data
        $formData = $this->transformer->transform($originalIdentifiers);

        // Then reverse transform back
        $result = $this->transformer->reverseTransform($formData);

        // Should get back the same identifiers
        $this->assertEqualsCanonicalizing($originalIdentifiers, $result);
    }

    private function prepareRolesStub(): void
    {
        $this->roleRegistryStub
            ->method('getRoles')
            ->willReturnMap([
                [AdminContext::class, $this->sectionRoles],
            ]);

        $this->roleRegistryStub
            ->method('getRole')
            ->willReturnCallback(function (string $roleIdentifier, string $context) {
                $roleConstant = RoleIdentifierHelper::getRoleConstantFromIdentifier($roleIdentifier);

                foreach ($this->sectionRoles as $role) {
                    if ($role->getConstant() === $roleConstant) {
                        return $role;
                    }
                }

                throw new InvalidArgumentException(sprintf('Role "%s" not found', $roleConstant));
            });
    }
}
