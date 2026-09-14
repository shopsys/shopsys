<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Security\Attribute;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopsys\AdministrationBundle\Component\Attributes\CrudAction;
use Shopsys\AdministrationBundle\Component\Security\Attribute\CrudAttributeProcessor;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\PublicAccess;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Symfony\Component\HttpFoundation\Response;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudController;

class CrudAttributeProcessorTest extends TestCase
{
    private const string CRUD_ROLE = 'ROLE_CRUD_REVIEW';

    public function testCanAttributeWithoutRoleUsesTheCrudControllerRole(): void
    {
        $rules = $this->process(ReviewCrudController::class, 'approveAction');

        $this->assertCount(1, $rules);
        $this->assertSame(self::CRUD_ROLE . '_EDIT', $rules[0]->roleIdentifier);
    }

    public function testForRoleOfTheHandlingClassTakesPrecedenceOverTheCrudControllerRole(): void
    {
        $handler = new #[ForRole('ROLE_MAILING')] class {
            #[CrudAction]
            #[CanEdit]
            public function sendEmailAction(): void
            {
            }
        };

        $rules = $this->process($handler, 'sendEmailAction');

        $this->assertSame('ROLE_MAILING_EDIT', $rules[0]->roleIdentifier);
    }

    public function testExplicitRoleOfTheAttributeWins(): void
    {
        $handler = new class() {
            #[CrudAction]
            #[CanEdit(role: 'ROLE_MODERATOR')]
            public function approveAction(): void
            {
            }
        };

        $rules = $this->process($handler, 'approveAction');

        $this->assertSame('ROLE_MODERATOR_EDIT', $rules[0]->roleIdentifier);
    }

    public function testBuiltInActionInheritsTheAttributesOfAbstractCrudController(): void
    {
        $rules = $this->process(ReviewCrudController::class, 'editAction');

        $this->assertCount(2, $rules);
        $this->assertSame(self::CRUD_ROLE . '_EDIT', $rules[0]->roleIdentifier);
        $this->assertSame([HttpMethod::POST], $rules[0]->httpMethods);
        $this->assertSame(self::CRUD_ROLE . '_VIEW', $rules[1]->roleIdentifier);
        $this->assertSame([HttpMethod::GET], $rules[1]->httpMethods);
    }

    public function testSuperAdminOnlyOnTheCrudControllerRestrictsItsBuiltInActions(): void
    {
        $controller = new #[SuperAdminOnly] class() extends ReviewCrudController {
        };

        $rules = $this->process($controller, 'listAction');

        $this->assertCount(1, $rules);
        $this->assertSame(SystemRole::SUPER_ADMIN, $rules[0]->roleIdentifier);
    }

    public function testRequireRoleAndPublicAccessOnCustomActionsAreRespected(): void
    {
        $handler = new class() {
            #[CrudAction]
            #[RequireRole('ROLE_MODERATOR')]
            public function moderateAction(): void
            {
            }

            #[CrudAction]
            #[PublicAccess]
            public function pingAction(): void
            {
            }
        };

        $this->assertSame('ROLE_MODERATOR', $this->process($handler, 'moderateAction')[0]->roleIdentifier);
        $this->assertSame(SystemRole::PUBLIC_ACCESS, $this->process($handler, 'pingAction')[0]->roleIdentifier);
    }

    public function testActionWithoutAccessControlAttributeIsRejected(): void
    {
        $handler = new class() {
            #[CrudAction]
            public function publishAction(): void
            {
            }
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('must be guarded by an access control attribute');

        $this->process($handler, 'publishAction');
    }

    public function testOverridingMethodKeepsTheAttributesOfTheParentDeclaration(): void
    {
        $controller = new class() extends ReviewCrudController {
            public function approveAction(int $id): Response
            {
                return parent::approveAction($id);
            }
        };

        $rules = $this->process($controller, 'approveAction');

        $this->assertSame(self::CRUD_ROLE . '_EDIT', $rules[0]->roleIdentifier);
    }

    public function testMethodWithoutCrudActionAttributeIsNotProcessed(): void
    {
        $handler = new class() {
            #[CanEdit]
            public function helperAction(): void
            {
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is not a CRUD action');

        $this->process($handler, 'helperAction');
    }

    /**
     * @param class-string|object $class
     * @return list<\Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData>
     */
    private function process(string|object $class, string $method): array
    {
        $reflectionClass = new ReflectionClass($class);

        return new CrudAttributeProcessor()->processCrudAction($reflectionClass, $reflectionClass->getMethod($method), self::CRUD_ROLE);
    }
}
