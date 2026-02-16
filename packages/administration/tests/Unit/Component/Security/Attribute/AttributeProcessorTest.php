<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Security\Attribute;

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleFactory;
use Shopsys\AdministrationBundle\Component\Security\Attribute\AttributeProcessor;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\PublicAccess;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequirePermission;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;

class AttributeProcessorTest extends TestCase
{
    private AttributeProcessor $processor;

    private RoleRegistryInterface&Stub $roleRegistry;

    #[Override]
    protected function setUp(): void
    {
        $this->roleRegistry = $this->createStub(RoleRegistryInterface::class);
        $accessControlRuleFactory = new AccessControlRuleFactory($this->roleRegistry);
        $this->processor = new AttributeProcessor($accessControlRuleFactory);
    }

    /**
     * @param array<string, string> $roleMappings
     */
    private function setupRoleRegistry(array $roleMappings): void
    {
        $this->roleRegistry
            ->method('getRole')
            ->willReturnCallback(function (string $identifier, string $context) use ($roleMappings) {
                if (!isset($roleMappings[$identifier])) {
                    throw new InvalidArgumentException("Role not found: {$identifier}");
                }

                $role = $this->createStub(Role::class);
                $role->method('getConstant')->willReturn($roleMappings[$identifier]);

                return $role;
            });
    }

    public function testProcessMethodWithCanViewAttribute(): void
    {
        $this->setupRoleRegistry(['ROLE_USER_VIEW' => 'ROLE_USER']);

        $testClass = new class() {
            #[CanView(role: 'ROLE_USER')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_USER_VIEW', $rules[0]->getRoleIdentifier());
        $this->assertEmpty($rules[0]->httpMethods);
    }

    public function testProcessMethodWithCanEditAttribute(): void
    {
        $this->setupRoleRegistry(['ROLE_ADMIN_EDIT' => 'ROLE_ADMIN']);

        $testClass = new class() {
            #[CanEdit(role: 'ROLE_ADMIN')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_ADMIN_EDIT', $rules[0]->getRoleIdentifier());
    }

    public function testProcessMethodWithCanCreateAttribute(): void
    {
        $this->setupRoleRegistry(['ROLE_MANAGER_CREATE' => 'ROLE_MANAGER']);

        $testClass = new class() {
            #[CanCreate(role: 'ROLE_MANAGER')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_MANAGER_CREATE', $rules[0]->getRoleIdentifier());
    }

    public function testProcessMethodWithCanDeleteAttribute(): void
    {
        $this->setupRoleRegistry(['ROLE_ADMIN_DELETE' => 'ROLE_ADMIN']);

        $testClass = new class() {
            #[CanDelete(role: 'ROLE_ADMIN')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_ADMIN_DELETE', $rules[0]->getRoleIdentifier());
    }

    public function testProcessMethodWithHttpMethodRestrictions(): void
    {
        $this->setupRoleRegistry(['ROLE_USER_VIEW' => 'ROLE_USER']);

        $testClass = new class() {
            #[CanView(role: 'ROLE_USER', methods: [HttpMethod::GET, HttpMethod::POST])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_USER_VIEW', $rules[0]->getRoleIdentifier());
        $this->assertCount(2, $rules[0]->httpMethods);
        $this->assertEquals([HttpMethod::GET, HttpMethod::POST], $rules[0]->httpMethods);
    }

    public function testProcessMethodWithClassLevelForRole(): void
    {
        $this->setupRoleRegistry(['ROLE_EDITOR_VIEW' => 'ROLE_EDITOR']);

        $testClass = new #[ForRole('ROLE_EDITOR')] class {
            #[CanView]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_EDITOR_VIEW', $rules[0]->getRoleIdentifier());
    }

    public function testProcessMethodWithoutRoleThrowsException(): void
    {
        $testClass = new class() {
            #[CanView]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Role must be specified either in Shopsys\FrameworkBundle\Component\Security\Attribute\CanView attribute or class-level ForRole attribute');

        $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));
    }

    public function testProcessMethodWithRequireRole(): void
    {
        $this->setupRoleRegistry([
            'ROLE_ADMIN' => 'ROLE_ADMIN',
            'ROLE_MANAGER' => 'ROLE_MANAGER',
        ]);

        $testClass = new class() {
            #[RequireRole(['ROLE_ADMIN', 'ROLE_MANAGER'])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(2, $rules);
        $this->assertEquals('ROLE_ADMIN', $rules[0]->getRoleIdentifier());
        $this->assertEquals('ROLE_MANAGER', $rules[1]->getRoleIdentifier());
    }

    public function testProcessMethodWithRequirePermission(): void
    {
        $this->setupRoleRegistry(['ROLE_USER_VIEW' => 'ROLE_USER']);

        $testClass = new class() {
            #[RequirePermission(role: 'ROLE_USER', permission: Permission::VIEW)]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_USER_VIEW', $rules[0]->getRoleIdentifier());
    }

    public function testProcessMethodWithSuperAdminOnlyAtClassLevel(): void
    {
        $this->setupRoleRegistry([SystemRole::SUPER_ADMIN => SystemRole::SUPER_ADMIN]);

        $testClass = new #[SuperAdminOnly] class() {
            #[CanView(role: 'ROLE_USER')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals(SystemRole::SUPER_ADMIN, $rules[0]->getRoleIdentifier());
    }

    public function testProcessMethodWithSuperAdminOnlyAtMethodLevel(): void
    {
        $this->setupRoleRegistry([SystemRole::SUPER_ADMIN => SystemRole::SUPER_ADMIN]);

        $testClass = new class() {
            #[SuperAdminOnly]
            #[CanView(role: 'ROLE_USER')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals(SystemRole::SUPER_ADMIN, $rules[0]->getRoleIdentifier());
    }

    public function testProcessMethodWithPublicAccessAtMethodLevel(): void
    {
        $this->setupRoleRegistry([SystemRole::PUBLIC_ACCESS => SystemRole::PUBLIC_ACCESS]);

        $testClass = new class() {
            #[PublicAccess]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals(SystemRole::PUBLIC_ACCESS, $rules[0]->getRoleIdentifier());
    }

    public function testProcessMethodWithPublicAccessAtClassLevel(): void
    {
        $this->setupRoleRegistry([SystemRole::PUBLIC_ACCESS => SystemRole::PUBLIC_ACCESS]);

        $testClass = new #[PublicAccess] class() {
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals(SystemRole::PUBLIC_ACCESS, $rules[0]->getRoleIdentifier());
    }

    public function testPublicAccessIgnoredWhenOtherRulesExist(): void
    {
        $this->setupRoleRegistry(['ROLE_USER_VIEW' => 'ROLE_USER']);

        $testClass = new #[PublicAccess] class() {
            #[CanView(role: 'ROLE_USER')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_USER_VIEW', $rules[0]->getRoleIdentifier());
    }

    public function testProcessMethodWithMultipleAttributes(): void
    {
        $this->setupRoleRegistry([
            'ROLE_MANAGER' => 'ROLE_MANAGER',
            'ROLE_USER_VIEW' => 'ROLE_USER',
            'ROLE_ADMIN_EDIT' => 'ROLE_ADMIN',
        ]);

        $testClass = new class() {
            #[CanView(role: 'ROLE_USER')]
            #[CanEdit(role: 'ROLE_ADMIN')]
            #[RequireRole(['ROLE_MANAGER'])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(3, $rules);

        $roleIdentifiers = array_map(fn ($rule) => $rule->getRoleIdentifier(), $rules);
        $this->assertContains('ROLE_MANAGER', $roleIdentifiers);
        $this->assertContains('ROLE_USER_VIEW', $roleIdentifiers);
        $this->assertContains('ROLE_ADMIN_EDIT', $roleIdentifiers);
    }

    public function testProcessMethodWithNoAttributes(): void
    {
        $testClass = new class() {
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(0, $rules);
    }

    public function testMethodLevelAttributeOverridesClassLevelForSpecificPermission(): void
    {
        $this->setupRoleRegistry(['ROLE_SPECIAL_VIEW' => 'ROLE_SPECIAL']);

        $testClass = new #[ForRole('ROLE_DEFAULT')] class {
            #[CanView(role: 'ROLE_SPECIAL')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_SPECIAL_VIEW', $rules[0]->getRoleIdentifier());
    }

    public function testProcessMethodWithSuperAdminOnlyAndHttpMethods(): void
    {
        $this->setupRoleRegistry([SystemRole::SUPER_ADMIN => SystemRole::SUPER_ADMIN]);

        $testClass = new class() {
            #[SuperAdminOnly(methods: [HttpMethod::POST, HttpMethod::DELETE])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals(SystemRole::SUPER_ADMIN, $rules[0]->getRoleIdentifier());
        $this->assertCount(2, $rules[0]->httpMethods);
        $this->assertEquals([HttpMethod::POST, HttpMethod::DELETE], $rules[0]->httpMethods);
    }

    public function testProcessMethodWithPublicAccessAndHttpMethods(): void
    {
        $this->setupRoleRegistry([SystemRole::PUBLIC_ACCESS => SystemRole::PUBLIC_ACCESS]);

        $testClass = new class() {
            #[PublicAccess(methods: [HttpMethod::GET])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals(SystemRole::PUBLIC_ACCESS, $rules[0]->getRoleIdentifier());
        $this->assertCount(1, $rules[0]->httpMethods);
        $this->assertEquals([HttpMethod::GET], $rules[0]->httpMethods);
    }

    public function testProcessMethodWithRequireRoleAndHttpMethods(): void
    {
        $this->setupRoleRegistry(['ROLE_API' => 'ROLE_API']);

        $testClass = new class() {
            #[RequireRole(['ROLE_API'], methods: [HttpMethod::GET, HttpMethod::POST])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_API', $rules[0]->getRoleIdentifier());
        $this->assertCount(2, $rules[0]->httpMethods);
        $this->assertEquals([HttpMethod::GET, HttpMethod::POST], $rules[0]->httpMethods);
    }

    public function testProcessMethodWithRequirePermissionAndHttpMethods(): void
    {
        $this->setupRoleRegistry(['ROLE_USER_EDIT' => 'ROLE_USER']);

        $testClass = new class() {
            #[RequirePermission(role: 'ROLE_USER', permission: Permission::EDIT, methods: [HttpMethod::GET])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_USER_EDIT', $rules[0]->getRoleIdentifier());
        $this->assertCount(1, $rules[0]->httpMethods);
        $this->assertEquals([HttpMethod::GET], $rules[0]->httpMethods);
    }
}
