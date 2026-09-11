<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\DependencyInjection\Compiler;

use Override;
use ReflectionClass;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Crud\Action\CrudActionDefinition;
use Shopsys\AdministrationBundle\Component\Crud\Action\CrudActionDiscoverer;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\CrudRoleConstantProvider;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\AdministrationBundle\Component\Security\Attribute\CrudAttributeProcessor;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Collects the actions of CRUD controllers (built-in and custom, declared by the CrudAction attribute) with their access control rules
 * and stores them as a container parameter consumed by CrudControllerRegistry.
 * Runs after the CRUD controllers, their extensions and their role constants are resolved.
 */
final class LoadCrudActionsCompilerPass implements CompilerPassInterface
{
    public const string CRUD_ACTION_TAG = 'shopsys.admin.crud_action';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        /** @var array<int, array{class: class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, entityClass: string}> $crudControllers */
        $crudControllers = $container->getParameter(CrudControllerRegistry::CRUD_CONTROLLERS_PARAMETER);
        /** @var array<int, array{extensionClass: class-string, controllerClass: class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, priority: int}> $crudControllerExtensions */
        $crudControllerExtensions = $container->getParameter(CrudControllerRegistry::CRUD_CONTROLLERS_EXTENSIONS_PARAMETER);
        /** @var array<class-string, string|null> $customRoleConstants */
        $customRoleConstants = $container->getParameter(CrudRoleConstantProvider::CRUD_ROLE_CONSTANTS_PARAMETER);

        $crudControllerClasses = array_column($crudControllers, 'class');
        $controllerClassesByExtensionClass = array_column($crudControllerExtensions, 'controllerClass', 'extensionClass');
        $discoverer = new CrudActionDiscoverer();
        $crudAttributeProcessor = new CrudAttributeProcessor();

        $actions = [];

        foreach ($this->getClassesToScan($container, $crudControllerClasses) as $class) {
            $reflectionClass = new ReflectionClass($class);

            foreach ($discoverer->discover($reflectionClass, $crudControllerClasses, $controllerClassesByExtensionClass) as $action) {
                $actionKey = $action->crudControllerClass . '::' . $action->name;

                if (isset($actions[$actionKey])) {
                    throw new RuntimeException(sprintf(
                        'CRUD action "%s" of "%s" is declared twice, in %s() and in %s().',
                        $action->name,
                        $action->crudControllerClass,
                        $actions[$actionKey]->getControllerReference(),
                        $action->getControllerReference(),
                    ));
                }

                $roleConstant = CrudTransformationHelper::generateRoleConstant(
                    ReflectionHelper::getShortClassName($action->crudControllerClass),
                    $customRoleConstants[$action->crudControllerClass] ?? null,
                );
                $actions[$actionKey] = $action->withAccessControlRules(
                    $crudAttributeProcessor->processCrudAction($reflectionClass, $reflectionClass->getMethod($action->method), $roleConstant),
                );
            }
        }

        $container->setParameter(
            CrudControllerRegistry::CRUD_ACTIONS_PARAMETER,
            array_values(array_map(static fn (CrudActionDefinition $action): array => $action->toArray(), $actions)),
        );
    }

    /**
     * The CRUD controllers carry the built-in actions, the tagged services (autoconfigured by the CrudAction attribute) the custom ones
     *
     * @param class-string[] $crudControllerClasses
     * @return list<class-string>
     */
    private function getClassesToScan(ContainerBuilder $container, array $crudControllerClasses): array
    {
        $classes = $crudControllerClasses;

        foreach (array_keys($container->findTaggedServiceIds(self::CRUD_ACTION_TAG)) as $serviceId) {
            $class = $container->getDefinition($serviceId)->getClass();

            if ($class) {
                $classes[] = $class;
            }
        }

        return array_values(array_unique($classes));
    }
}
