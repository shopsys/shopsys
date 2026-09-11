<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Security\Attribute;

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
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
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;

class AttributeProcessorTest extends TestCase
{
    private AttributeProcessor $processor;

    #[Override]
    protected function setUp(): void
    {
        $this->processor = new AttributeProcessor();
    }

    public function testProcessMethodWithCanViewAttribute(): void
    {
        $testClass = new class() {
            #[CanView(role: 'ROLE_USER')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_USER_VIEW', $rules[0]->roleIdentifier);
        $this->assertEmpty($rules[0]->httpMethods);
    }

    public function testProcessMethodWithCanEditAttribute(): void
    {
        $testClass = new class() {
            #[CanEdit(role: 'ROLE_ADMIN')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_ADMIN_EDIT', $rules[0]->roleIdentifier);
    }

    public function testProcessMethodWithCanCreateAttribute(): void
    {
        $testClass = new class() {
            #[CanCreate(role: 'ROLE_MANAGER')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_MANAGER_CREATE', $rules[0]->roleIdentifier);
    }

    public function testProcessMethodWithCanDeleteAttribute(): void
    {
        $testClass = new class() {
            #[CanDelete(role: 'ROLE_ADMIN')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_ADMIN_DELETE', $rules[0]->roleIdentifier);
    }

    public function testProcessMethodWithHttpMethodRestrictions(): void
    {
        $testClass = new class() {
            #[CanView(role: 'ROLE_USER', methods: [HttpMethod::GET, HttpMethod::POST])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_USER_VIEW', $rules[0]->roleIdentifier);
        $this->assertCount(2, $rules[0]->httpMethods);
        $this->assertEquals([HttpMethod::GET, HttpMethod::POST], $rules[0]->httpMethods);
    }

    public function testProcessMethodWithClassLevelForRole(): void
    {
        $testClass = new #[ForRole('ROLE_EDITOR')] class {
            #[CanView]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_EDITOR_VIEW', $rules[0]->roleIdentifier);
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
        $testClass = new class() {
            #[RequireRole(['ROLE_ADMIN', 'ROLE_MANAGER'])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(2, $rules);
        $this->assertEquals('ROLE_ADMIN', $rules[0]->roleIdentifier);
        $this->assertEquals('ROLE_MANAGER', $rules[1]->roleIdentifier);
    }

    public function testProcessMethodWithRequirePermission(): void
    {
        $testClass = new class() {
            #[RequirePermission(role: 'ROLE_USER', permission: Permission::VIEW)]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_USER_VIEW', $rules[0]->roleIdentifier);
    }

    public function testProcessMethodWithSuperAdminOnlyAtClassLevel(): void
    {
        $testClass = new #[SuperAdminOnly] class() {
            #[CanView(role: 'ROLE_USER')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals(SystemRole::SUPER_ADMIN, $rules[0]->roleIdentifier);
    }

    public function testProcessMethodWithSuperAdminOnlyAtMethodLevel(): void
    {
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
        $this->assertEquals(SystemRole::SUPER_ADMIN, $rules[0]->roleIdentifier);
    }

    public function testProcessMethodWithPublicAccessAtMethodLevel(): void
    {
        $testClass = new class() {
            #[PublicAccess]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals(SystemRole::PUBLIC_ACCESS, $rules[0]->roleIdentifier);
    }

    public function testProcessMethodWithPublicAccessAtClassLevel(): void
    {
        $testClass = new #[PublicAccess] class() {
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals(SystemRole::PUBLIC_ACCESS, $rules[0]->roleIdentifier);
    }

    public function testPublicAccessIgnoredWhenOtherRulesExist(): void
    {
        $testClass = new #[PublicAccess] class() {
            #[CanView(role: 'ROLE_USER')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_USER_VIEW', $rules[0]->roleIdentifier);
    }

    public function testProcessMethodWithMultipleAttributes(): void
    {
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

        $roleIdentifiers = array_map(fn ($rule) => $rule->roleIdentifier, $rules);
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
        $testClass = new #[ForRole('ROLE_DEFAULT')] class {
            #[CanView(role: 'ROLE_SPECIAL')]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_SPECIAL_VIEW', $rules[0]->roleIdentifier);
    }

    public function testProcessMethodWithSuperAdminOnlyAndHttpMethods(): void
    {
        $testClass = new class() {
            #[SuperAdminOnly(methods: [HttpMethod::POST, HttpMethod::DELETE])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals(SystemRole::SUPER_ADMIN, $rules[0]->roleIdentifier);
        $this->assertCount(2, $rules[0]->httpMethods);
        $this->assertEquals([HttpMethod::POST, HttpMethod::DELETE], $rules[0]->httpMethods);
    }

    public function testProcessMethodWithPublicAccessAndHttpMethods(): void
    {
        $testClass = new class() {
            #[PublicAccess(methods: [HttpMethod::GET])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals(SystemRole::PUBLIC_ACCESS, $rules[0]->roleIdentifier);
        $this->assertCount(1, $rules[0]->httpMethods);
        $this->assertEquals([HttpMethod::GET], $rules[0]->httpMethods);
    }

    public function testProcessMethodWithRequireRoleAndHttpMethods(): void
    {
        $testClass = new class() {
            #[RequireRole(['ROLE_API'], methods: [HttpMethod::GET, HttpMethod::POST])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_API', $rules[0]->roleIdentifier);
        $this->assertCount(2, $rules[0]->httpMethods);
        $this->assertEquals([HttpMethod::GET, HttpMethod::POST], $rules[0]->httpMethods);
    }

    public function testProcessMethodWithRequirePermissionAndHttpMethods(): void
    {
        $testClass = new class() {
            #[RequirePermission(role: 'ROLE_USER', permission: Permission::EDIT, methods: [HttpMethod::GET])]
            public function testMethod(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($testClass);
        $rules = $this->processor->processMethod($reflectionClass, $reflectionClass->getMethod('testMethod'));

        $this->assertCount(1, $rules);
        $this->assertEquals('ROLE_USER_EDIT', $rules[0]->roleIdentifier);
        $this->assertCount(1, $rules[0]->httpMethods);
        $this->assertEquals([HttpMethod::GET], $rules[0]->httpMethods);
    }
}
