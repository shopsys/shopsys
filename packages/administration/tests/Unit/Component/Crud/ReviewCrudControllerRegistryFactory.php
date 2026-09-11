<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Crud;

use ReflectionClass;
use Shopsys\AdministrationBundle\Component\Crud\Action\CrudActionDefinition;
use Shopsys\AdministrationBundle\Component\Crud\Action\CrudActionDiscoverer;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\CrudRoleConstantProvider;
use Shopsys\AdministrationBundle\Component\Security\Attribute\CrudAttributeProcessor;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use stdClass;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudController;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudControllerExtension;

/**
 * Builds a real CrudControllerRegistry (the class is final) with the ReviewCrudController fixture registered,
 * its actions (built-in ones and the approve / export_all custom ones) are discovered the same way the compiler pass does it
 */
class ReviewCrudControllerRegistryFactory
{
    public const string ROLE_CONSTANT = 'ROLE_CRUD_REVIEW';

    /**
     * @param array<int, array<string, mixed>> $extraActions additional actions in the shape stored by LoadCrudActionsCompilerPass
     * @param \Shopsys\AdministrationBundle\Controller\AbstractCrudController|null $controller instance whose configure() is used, so a test can disable actions or set a route prefix
     * @param \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension|null $extension registered as an extension of the fixture controller
     */
    public static function create(
        array $extraActions = [],
        ?AbstractCrudController $controller = null,
        ?AbstractCrudControllerExtension $extension = null,
    ): CrudControllerRegistry {
        $controller ??= new ReviewCrudController();
        $services = [ReviewCrudController::class => static fn () => $controller];
        $extensions = [];
        $actions = array_map(static fn (CrudActionDefinition $action): array => $action->toArray(), self::createActionDefinitions());

        if ($extension !== null) {
            $services[ReviewCrudControllerExtension::class] = static fn () => $extension;
            $extensions[] = ['extensionClass' => ReviewCrudControllerExtension::class, 'controllerClass' => ReviewCrudController::class, 'priority' => 0];
        }

        return new CrudControllerRegistry(
            new EntityNameResolver([]),
            new CrudRoleConstantProvider(),
            new ServiceLocator($services),
            new ServiceLocator([]),
            [['class' => ReviewCrudController::class, 'entityClass' => stdClass::class]],
            $extensions,
            [...array_values($actions), ...$extraActions],
        );
    }

    /**
     * Returns the actions of the fixture controller (built-in and its own custom ones) with their access control rules, indexed by name
     *
     * @return array<string, \Shopsys\AdministrationBundle\Component\Crud\Action\CrudActionDefinition>
     */
    public static function createActionDefinitions(): array
    {
        $reflectionClass = new ReflectionClass(ReviewCrudController::class);
        $crudAttributeProcessor = new CrudAttributeProcessor();
        $actions = [];

        foreach (new CrudActionDiscoverer()->discover($reflectionClass, [ReviewCrudController::class], []) as $action) {
            $actions[$action->name] = $action->withAccessControlRules(
                $crudAttributeProcessor->processCrudAction($reflectionClass, $reflectionClass->getMethod($action->method), self::ROLE_CONSTANT),
            );
        }

        return $actions;
    }

    /**
     * Returns the data of an additional entity-bound custom action of the fixture controller guarded by EDIT permission of its role
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function createActionData(string $name, array $overrides = []): array
    {
        return array_merge([
            'name' => $name,
            'crudControllerClass' => ReviewCrudController::class,
            'controllerClass' => ReviewCrudController::class,
            'method' => $name . 'Action',
            'path' => '/' . $name . '/{id}',
            'entityBound' => true,
            'methods' => [],
            'requirements' => [],
            'defaults' => [],
            'condition' => null,
            'accessControlRules' => [['roleIdentifier' => self::ROLE_CONSTANT . '_EDIT', 'httpMethods' => []]],
        ], $overrides);
    }
}
