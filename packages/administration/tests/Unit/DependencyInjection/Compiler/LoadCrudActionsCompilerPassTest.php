<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\DependencyInjection\Compiler;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Attributes\CrudAction;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\CrudRoleConstantProvider;
use Shopsys\AdministrationBundle\DependencyInjection\Compiler\LoadCrudActionsCompilerPass;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\PublicAccess;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\RecalculateReviewsAction;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudController;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudControllerExtension;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewMailController;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\UnguardedReviewAction;

class LoadCrudActionsCompilerPassTest extends TestCase
{
    public function testActionsDeclaredInAllSupportedPlacesAreCollected(): void
    {
        $container = $this->createContainer([
            ReviewCrudController::class,
            ReviewCrudControllerExtension::class,
            ReviewMailController::class,
            RecalculateReviewsAction::class,
        ]);

        new LoadCrudActionsCompilerPass()->process($container);

        $actions = $this->getActionsByName($container);
        $this->assertEqualsCanonicalizing(
            ['list', 'detail', 'create', 'edit', 'delete', 'approve', 'move', 'export_all', 'archive', 'send_email', 'recalculate'],
            array_keys($actions),
        );

        // built-in actions are discovered from AbstractCrudController for every registered controller
        $this->assertSame(ReviewCrudController::class . '::editAction', $actions['edit']['controllerClass'] . '::' . $actions['edit']['method']);
        $this->assertSame('/edit/{id}', $actions['edit']['path']);
        $this->assertTrue($actions['edit']['entityBound']);
        $this->assertSame([
            ['roleIdentifier' => 'ROLE_CRUD_REVIEW_EDIT', 'httpMethods' => ['POST']],
            ['roleIdentifier' => 'ROLE_CRUD_REVIEW_VIEW', 'httpMethods' => ['GET']],
        ], $actions['edit']['accessControlRules']);
        $this->assertSame('/', $actions['list']['path']);

        // Request and parameters with a default value are not route placeholders, the required scalar ones are
        $this->assertSame('/move/{id}/{position}', $actions['move']['path']);
        $this->assertTrue($actions['move']['entityBound']);

        $this->assertSame([
            'name' => 'approve',
            'crudControllerClass' => ReviewCrudController::class,
            'controllerClass' => ReviewCrudController::class,
            'method' => 'approveAction',
            'path' => '/approve/{id}',
            'entityBound' => true,
            'methods' => [],
            'requirements' => [],
            'defaults' => [],
            'condition' => null,
            'accessControlRules' => [['roleIdentifier' => 'ROLE_CRUD_REVIEW_EDIT', 'httpMethods' => []]],
        ], $actions['approve']);

        $this->assertSame([
            'name' => 'export_all',
            'crudControllerClass' => ReviewCrudController::class,
            'controllerClass' => ReviewCrudController::class,
            'method' => 'exportAction',
            'path' => '/export.csv',
            'entityBound' => false,
            'methods' => ['GET'],
            'requirements' => ['_format' => 'csv'],
            'defaults' => ['_format' => 'csv'],
            'condition' => 'request.isXmlHttpRequest()',
            'accessControlRules' => [['roleIdentifier' => 'ROLE_CRUD_REVIEW_VIEW', 'httpMethods' => []]],
        ], $actions['export_all']);

        $this->assertSame(ReviewCrudController::class, $actions['archive']['crudControllerClass']);
        $this->assertSame(ReviewCrudControllerExtension::class, $actions['archive']['controllerClass']);
        $this->assertSame([['roleIdentifier' => 'ROLE_CRUD_REVIEW_DELETE', 'httpMethods' => []]], $actions['archive']['accessControlRules']);

        $this->assertSame('/send-email/{id}', $actions['send_email']['path']);
        $this->assertSame(ReviewMailController::class . '::sendEmailAction', $actions['send_email']['controllerClass'] . '::' . $actions['send_email']['method']);
        $this->assertSame([['roleIdentifier' => 'ROLE_MAILING_EDIT', 'httpMethods' => []]], $actions['send_email']['accessControlRules'], 'RequirePermission names its own role');

        $this->assertSame(RecalculateReviewsAction::class . '::__invoke', $actions['recalculate']['controllerClass'] . '::' . $actions['recalculate']['method']);
        $this->assertSame('/recalculate', $actions['recalculate']['path']);
        $this->assertFalse($actions['recalculate']['entityBound']);
        $this->assertSame([['roleIdentifier' => SystemRole::SUPER_ADMIN, 'httpMethods' => []]], $actions['recalculate']['accessControlRules']);
    }

    public function testActionWithoutAccessControlAttributeFailsTheBuild(): void
    {
        $container = $this->createContainer([UnguardedReviewAction::class]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('must be guarded by an access control attribute');

        new LoadCrudActionsCompilerPass()->process($container);
    }

    public function testPlaceholderWithoutMethodParameterIsAllowed(): void
    {
        $action = new class() {
            #[CrudAction(crudController: ReviewCrudController::class, path: '/publish/{id}/{note}')]
            #[CanView]
            public function publishAction(int $id): void
            {
            }
        };
        $container = $this->createContainer([$action::class]);

        new LoadCrudActionsCompilerPass()->process($container);

        $this->assertSame('/publish/{id}/{note}', $this->getActionsByName($container)['publish']['path']);
    }

    public function testPublicAccessMakesTheActionPublic(): void
    {
        $public = new class() {
            #[CrudAction(crudController: ReviewCrudController::class)]
            #[PublicAccess]
            public function pingAction(): void
            {
            }
        };
        $container = $this->createContainer([$public::class]);

        new LoadCrudActionsCompilerPass()->process($container);

        $this->assertSame([['roleIdentifier' => SystemRole::PUBLIC_ACCESS, 'httpMethods' => []]], $this->getActionsByName($container)['ping']['accessControlRules']);
    }

    public function testDuplicateActionNameForTheSameControllerIsRejected(): void
    {
        $duplicate = new class() {
            #[CrudAction(crudController: ReviewCrudController::class, name: 'approve')]
            #[CanView]
            public function anotherApproveAction(int $id): void
            {
            }
        };
        $container = $this->createContainer([ReviewCrudController::class, $duplicate::class]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is declared twice');

        new LoadCrudActionsCompilerPass()->process($container);
    }

    public function testCustomActionNamedLikeBuiltInActionIsRejectedAsDuplicate(): void
    {
        $duplicate = new class() {
            #[CrudAction(crudController: ReviewCrudController::class, name: 'edit')]
            #[CanView]
            public function anotherEditAction(int $id): void
            {
            }
        };
        $container = $this->createContainer([$duplicate::class]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CRUD action "edit" of "' . ReviewCrudController::class . '" is declared twice');

        new LoadCrudActionsCompilerPass()->process($container);
    }

    #[DataProvider('invalidTargetDataProvider')]
    #[DataProvider('invalidNameDataProvider')]
    #[DataProvider('invalidRouteDataProvider')]
    public function testInvalidDeclarationsAreRejectedAtBuildTime(object $service, string $expectedMessagePart): void
    {
        $container = $this->createContainer([$service::class]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expectedMessagePart);

        new LoadCrudActionsCompilerPass()->process($container);
    }

    /**
     * @return iterable<string, array{service: object, expectedMessagePart: string}>
     */
    public static function invalidTargetDataProvider(): iterable
    {
        yield 'target controller cannot be inferred' => [
            'service' => new class() {
                #[CrudAction]
                #[CanView]
                public function publishAction(): void
                {
                }
            },
            'expectedMessagePart' => 'the target CRUD controller cannot be inferred',
        ];

        yield 'target is not a registered CRUD controller' => [
            'service' => new class() {
                #[CrudAction(crudController: ReviewMailController::class)]
                #[CanView]
                public function publishAction(): void
                {
                }
            },
            'expectedMessagePart' => 'is not a registered CRUD controller',
        ];
    }

    /**
     * @return iterable<string, array{service: object, expectedMessagePart: string}>
     */
    public static function invalidNameDataProvider(): iterable
    {
        yield 'name not in snake_case' => [
            'service' => new class() {
                #[CrudAction(crudController: ReviewCrudController::class, name: 'Publish-Now')]
                #[CanView]
                public function publishAction(): void
                {
                }
            },
            'expectedMessagePart' => 'must be in snake_case',
        ];
    }

    /**
     * @return iterable<string, array{service: object, expectedMessagePart: string}>
     */
    public static function invalidRouteDataProvider(): iterable
    {
        yield 'attribute of unknown class' => [
            'service' => new class() {
                #[CrudAction(crudController: ReviewCrudController::class)]
                // the discovery must report an attribute of a class that does not exist (typically a missing use statement)
                #[UnknownAttribute] // @phpstan-ignore attribute.notFound
                public function publishAction(): void
                {
                }
            },
            'expectedMessagePart' => 'the attribute class "Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\UnknownAttribute" does not exist',
        ];

        yield 'required scalar parameter missing in the explicit path' => [
            'service' => new class() {
                #[CrudAction(crudController: ReviewCrudController::class, path: '/publish/{id}')]
                #[CanView]
                public function publishAction(int $id, int $position): void
                {
                }
            },
            'expectedMessagePart' => 'the required parameter $position is not a placeholder of the route path "/publish/{id}"',
        ];

        yield 'non-public method' => [
            'service' => new class() {
                #[CrudAction(crudController: ReviewCrudController::class)]
                #[CanView]
                protected function publishAction(): void
                {
                }
            },
            'expectedMessagePart' => 'must be public',
        ];

        yield 'reserved route default' => [
            'service' => new class() {
                #[CrudAction(crudController: ReviewCrudController::class, defaults: ['_crud_action' => 'edit'])]
                #[CanView]
                public function publishAction(): void
                {
                }
            },
            'expectedMessagePart' => 'the route default "_crud_action" is reserved',
        ];

        yield 'invokable class without name' => [
            'service' => new #[CrudAction(crudController: ReviewCrudController::class)] #[SuperAdminOnly] class() {
                public function __invoke(): void
                {
                }
            },
            'expectedMessagePart' => 'the action name cannot be derived from __invoke()',
        ];

        yield 'class-level attribute without __invoke()' => [
            'service' => new #[CrudAction(crudController: ReviewCrudController::class, name: 'publish')] class() {
                #[CanView]
                public function publishAction(): void
                {
                }
            },
            'expectedMessagePart' => 'but no __invoke() method',
        ];
    }

    /**
     * @param class-string[] $taggedServiceClasses
     */
    private function createContainer(array $taggedServiceClasses): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter(CrudControllerRegistry::CRUD_CONTROLLERS_PARAMETER, [
            ['class' => ReviewCrudController::class, 'entityClass' => stdClass::class],
        ]);
        $container->setParameter(CrudControllerRegistry::CRUD_CONTROLLERS_EXTENSIONS_PARAMETER, [
            ['extensionClass' => ReviewCrudControllerExtension::class, 'controllerClass' => ReviewCrudController::class, 'priority' => 0],
        ]);
        $container->setParameter(CrudRoleConstantProvider::CRUD_ROLE_CONSTANTS_PARAMETER, [ReviewCrudController::class => null]);

        // anonymous class names are not valid service ids, so the services get generated ids
        foreach ($taggedServiceClasses as $index => $class) {
            $definition = new Definition($class);
            $definition->addTag(LoadCrudActionsCompilerPass::CRUD_ACTION_TAG);
            $container->setDefinition('crud_action_service_' . $index, $definition);
        }

        return $container;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getActionsByName(ContainerBuilder $container): array
    {
        /** @var array<int, array<string, mixed>> $actions */
        $actions = $container->getParameter(CrudControllerRegistry::CRUD_ACTIONS_PARAMETER);

        return array_column($actions, null, 'name');
    }
}
