<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Security\Role;

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\CoreRoleProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistry;
use Tests\FrameworkBundle\Unit\Component\Context\TestContextA;
use Tests\FrameworkBundle\Unit\Component\Context\TestContextB;

class RoleRegistryTest extends TestCase
{
    private const TEST_ADMIN_CONTEXT = TestContextA::class;
    private const TEST_API_CONTEXT = TestContextB::class;

    private ContextResolverInterface $contextResolver;

    #[Override]
    protected function setUp(): void
    {
        $this->contextResolver = $this->createMock(ContextResolverInterface::class);
        $this->contextResolver
            ->method('validateContextClass')
            ->willReturnCallback(function (string $context): void {
                if (!in_array($context, [self::TEST_ADMIN_CONTEXT, self::TEST_API_CONTEXT], true)) {
                    throw new InvalidArgumentException("Invalid context: {$context}");
                }
            });
    }

    public function testConstructorValidatesProviders(): void
    {
        $invalidProvider = $this->createMock(RoleProviderInterface::class);
        $invalidProvider->method('getTargetContext')->willReturn('InvalidContext');
        $invalidProvider->method('getPriority')->willReturn(10);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid context: InvalidContext');

        new RoleRegistry([$invalidProvider], $this->contextResolver);
    }

    public function testConstructorValidatesProviderPriority(): void
    {
        $invalidProvider = $this->createMock(RoleProviderInterface::class);
        $invalidProvider->method('getTargetContext')->willReturn(self::TEST_ADMIN_CONTEXT);
        $invalidProvider->method('getPriority')->willReturn(0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Role provider "MockObject_RoleProviderInterface_');

        new RoleRegistry([$invalidProvider], $this->contextResolver);
    }

    public function testConstructorAllowsCoreProviderWithZeroPriority(): void
    {
        $coreProvider = $this->createMock(CoreRoleProviderInterface::class);
        $coreProvider->method('getTargetContext')->willReturn(self::TEST_ADMIN_CONTEXT);
        $coreProvider->method('getPriority')->willReturn(0);
        $coreProvider->method('configureRoles')->willReturnCallback(function (): void {});

        // Should not throw exception
        $registry = new RoleRegistry([$coreProvider], $this->contextResolver);
        $this->addToAssertionCount(1); // Just verify no exception was thrown
    }

    public function testGetRoleReturnsValidRole(): void
    {
        $provider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_ADMIN', 'Administrator', [Permission::FULL]));
        });

        $registry = new RoleRegistry([$provider], $this->contextResolver);
        $role = $registry->getRole('ROLE_ADMIN', self::TEST_ADMIN_CONTEXT);

        $this->assertEquals('ROLE_ADMIN', $role->getConstant());
        $this->assertEquals('Administrator', $role->getName());
    }

    public function testGetRoleWithPermissionIdentifier(): void
    {
        $provider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_USER', 'User', [Permission::VIEW, Permission::EDIT]));
        });

        $registry = new RoleRegistry([$provider], $this->contextResolver);
        $role = $registry->getRole('ROLE_USER_VIEW', self::TEST_ADMIN_CONTEXT);

        $this->assertEquals('ROLE_USER', $role->getConstant());
        $this->assertTrue($role->hasPermission(Permission::VIEW));
    }

    public function testGetRoleThrowsExceptionForNonExistentRole(): void
    {
        $provider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_ADMIN', 'Administrator', [Permission::FULL]));
        });

        $registry = new RoleRegistry([$provider], $this->contextResolver);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Role "ROLE_NONEXISTENT" not found in role registry for context "' . self::TEST_ADMIN_CONTEXT . '"');

        $registry->getRole('ROLE_NONEXISTENT', self::TEST_ADMIN_CONTEXT);
    }

    public function testGetRoleThrowsExceptionForUnavailablePermission(): void
    {
        $provider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_VIEWER', 'Viewer', [Permission::VIEW]));
        });

        $registry = new RoleRegistry([$provider], $this->contextResolver);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Role "ROLE_VIEWER" does not have permission "EDIT". Available permissions: VIEW');

        $registry->getRole('ROLE_VIEWER_EDIT', self::TEST_ADMIN_CONTEXT);
    }

    public function testGetRolesReturnsAllRolesForContext(): void
    {
        $provider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_ADMIN', 'Administrator', [Permission::FULL]));
            $collection->add(new Role('ROLE_USER', 'User', [Permission::VIEW]));
        });

        $registry = new RoleRegistry([$provider], $this->contextResolver);
        $roles = $registry->getRoles(self::TEST_ADMIN_CONTEXT);

        $this->assertCount(2, $roles);
        $roleConstants = array_map(fn (Role $role) => $role->getConstant(), $roles);
        $this->assertContains('ROLE_ADMIN', $roleConstants);
        $this->assertContains('ROLE_USER', $roleConstants);
    }

    public function testMultipleProvidersProcessedInPriorityOrder(): void
    {
        $collectedRoles = [];

        $provider1 = $this->createProvider(self::TEST_ADMIN_CONTEXT, 20, function (RoleCollection $collection) use (&$collectedRoles): void {
            $collectedRoles[] = 'provider1';
            $collection->add(new Role('ROLE_USER', 'User', [Permission::VIEW]));
        });

        $provider2 = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection) use (&$collectedRoles): void {
            $collectedRoles[] = 'provider2';
            $collection->add(new Role('ROLE_ADMIN', 'Admin', [Permission::FULL]));
        });

        $registry = new RoleRegistry([$provider1, $provider2], $this->contextResolver);
        $registry->getRoles(self::TEST_ADMIN_CONTEXT);

        // Lower priority (10) should be processed before higher priority (20)
        $this->assertEquals(['provider2', 'provider1'], $collectedRoles);
    }

    public function testProvidersGroupedByContext(): void
    {
        $adminProvider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_ADMIN', 'Admin', [Permission::FULL]));
        });

        $apiProvider = $this->createProvider(self::TEST_API_CONTEXT, 10, function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_API', 'API User', [Permission::VIEW]));
        });

        $registry = new RoleRegistry([$adminProvider, $apiProvider], $this->contextResolver);

        $adminRoles = $registry->getRoles(self::TEST_ADMIN_CONTEXT);
        $apiRoles = $registry->getRoles(self::TEST_API_CONTEXT);

        $this->assertCount(1, $adminRoles);
        $this->assertCount(1, $apiRoles);
        $this->assertEquals('ROLE_ADMIN', $adminRoles[0]->getConstant());
        $this->assertEquals('ROLE_API', $apiRoles[0]->getConstant());
    }

    public function testRoleCollectionsCachedPerContext(): void
    {
        $callCount = 0;
        $provider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection) use (&$callCount): void {
            $callCount++;
            $collection->add(new Role('ROLE_ADMIN', 'Admin', [Permission::FULL]));
        });

        $registry = new RoleRegistry([$provider], $this->contextResolver);

        // First call should trigger provider
        $registry->getRoles(self::TEST_ADMIN_CONTEXT);
        $this->assertEquals(1, $callCount);

        // Second call should use cached collection
        $registry->getRoles(self::TEST_ADMIN_CONTEXT);
        $this->assertEquals(1, $callCount);
    }

    public function testProviderExceptionWrappedWithContext(): void
    {
        $provider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection): void {
            throw new RuntimeException('Provider failed');
        });

        $registry = new RoleRegistry([$provider], $this->contextResolver);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error processing role provider "MockObject_RoleProviderInterface_');

        $registry->getRoles(self::TEST_ADMIN_CONTEXT);
    }

    public function testEmptyProviderListHandledGracefully(): void
    {
        $registry = new RoleRegistry([], $this->contextResolver);
        $roles = $registry->getRoles(self::TEST_ADMIN_CONTEXT);

        $this->assertIsArray($roles);
        $this->assertEmpty($roles);
    }

    public function testGetRoleWithFullPermissionIncludesSubordinatePermissions(): void
    {
        $provider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_ADMIN', 'Administrator', [Permission::FULL]));
        });

        $registry = new RoleRegistry([$provider], $this->contextResolver);

        // Should work with any permission since FULL includes all
        $role = $registry->getRole('ROLE_ADMIN_VIEW', self::TEST_ADMIN_CONTEXT);
        $this->assertEquals('ROLE_ADMIN', $role->getConstant());

        $role = $registry->getRole('ROLE_ADMIN_EDIT', self::TEST_ADMIN_CONTEXT);
        $this->assertEquals('ROLE_ADMIN', $role->getConstant());

        $role = $registry->getRole('ROLE_ADMIN_DELETE', self::TEST_ADMIN_CONTEXT);
        $this->assertEquals('ROLE_ADMIN', $role->getConstant());
    }

    public function testCoreProviderCanHaveNegativePriority(): void
    {
        $coreProvider = $this->createMock(CoreRoleProviderInterface::class);
        $coreProvider->method('getTargetContext')->willReturn(self::TEST_ADMIN_CONTEXT);
        $coreProvider->method('getPriority')->willReturn(-10);
        $coreProvider->method('configureRoles')->willReturnCallback(function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_SYSTEM', 'System', [Permission::FULL]));
        });

        $regularProvider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_USER', 'User', [Permission::VIEW]));
        });

        // Should not throw exception
        $registry = new RoleRegistry([$coreProvider, $regularProvider], $this->contextResolver);
        $roles = $registry->getRoles(self::TEST_ADMIN_CONTEXT);

        $this->assertCount(2, $roles);
    }

    public function testContextValidationCalledForGetRole(): void
    {
        $contextResolver = $this->createMock(ContextResolverInterface::class);
        $contextResolver
            ->method('validateContextClass')
            ->willReturnCallback(function (string $context): void {
                if (!in_array($context, [self::TEST_ADMIN_CONTEXT, self::TEST_API_CONTEXT], true)) {
                    throw new InvalidArgumentException("Invalid context: {$context}");
                }
            });

        $provider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_ADMIN', 'Admin', [Permission::FULL]));
        });

        $contextResolver
            ->expects($this->atLeastOnce())
            ->method('validateContextClass')
            ->with(self::TEST_ADMIN_CONTEXT);

        $registry = new RoleRegistry([$provider], $contextResolver);
        $registry->getRole('ROLE_ADMIN', self::TEST_ADMIN_CONTEXT);
    }

    public function testContextValidationCalledForGetRoles(): void
    {
        $contextResolver = $this->createMock(ContextResolverInterface::class);
        $contextResolver
            ->method('validateContextClass')
            ->willReturnCallback(function (string $context): void {
                if (!in_array($context, [self::TEST_ADMIN_CONTEXT, self::TEST_API_CONTEXT], true)) {
                    throw new InvalidArgumentException("Invalid context: {$context}");
                }
            });

        $provider = $this->createProvider(self::TEST_ADMIN_CONTEXT, 10, function (RoleCollection $collection): void {
            $collection->add(new Role('ROLE_ADMIN', 'Admin', [Permission::FULL]));
        });

        $contextResolver
            ->expects($this->atLeastOnce())
            ->method('validateContextClass')
            ->with(self::TEST_ADMIN_CONTEXT);

        $registry = new RoleRegistry([$provider], $contextResolver);
        $registry->getRoles(self::TEST_ADMIN_CONTEXT);
    }

    private function createProvider(string $context, int $priority, callable $configureCallback): RoleProviderInterface
    {
        $provider = $this->createMock(RoleProviderInterface::class);
        $provider->method('getTargetContext')->willReturn($context);
        $provider->method('getPriority')->willReturn($priority);
        $provider->method('configureRoles')->willReturnCallback($configureCallback);

        return $provider;
    }
}
