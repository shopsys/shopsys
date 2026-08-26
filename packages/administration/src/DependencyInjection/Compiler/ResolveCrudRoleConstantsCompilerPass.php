<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\DependencyInjection\Compiler;

use Override;
use ReflectionClass;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\CrudRoleConstantProvider;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Resolves the role constant of every CRUD controller from the class-level ForRole attribute of the controller and its extensions.
 * Must run after InitializeControllersCompilerPass and LoadControllersExtensionCompilerPass, whose parameters it reads.
 */
final class ResolveCrudRoleConstantsCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        /** @var array<int, array{class: class-string, entityClass: string}> $crudControllers */
        $crudControllers = $container->getParameter(CrudControllerRegistry::CRUD_CONTROLLERS_PARAMETER);
        /** @var array<int, array{extensionClass: class-string, controllerClass: class-string, priority: int}> $crudControllerExtensions */
        $crudControllerExtensions = $container->getParameter(CrudControllerRegistry::CRUD_CONTROLLERS_EXTENSIONS_PARAMETER);

        $extensionClassesByController = $this->groupExtensionClassesByController($crudControllerExtensions);
        $roleConstants = [];

        foreach ($crudControllers as $crudController) {
            $controllerClass = $crudController['class'];
            $reflectionClass = new ReflectionClass($controllerClass);
            $customRoleConstant = $this->findForRole($reflectionClass);

            // an extension may override the controller's role — the one whose configure() runs last (highest priority) wins
            foreach ($extensionClassesByController[$controllerClass] ?? [] as $extensionClass) {
                $customRoleConstant = $this->findForRole(new ReflectionClass($extensionClass)) ?? $customRoleConstant;
            }

            $roleConstants[$controllerClass] = [
                'roleConstant' => CrudTransformationHelper::generateRoleConstant($reflectionClass->getShortName(), $customRoleConstant),
                'customRoleConstant' => $customRoleConstant,
            ];
        }

        $container->setParameter(CrudRoleConstantProvider::CRUD_ROLE_CONSTANTS_PARAMETER, $roleConstants);
    }

    /**
     * @param array<int, array{extensionClass: class-string, controllerClass: class-string, priority: int}> $extensions sorted by ascending priority
     * @return array<class-string, list<class-string>>
     */
    private function groupExtensionClassesByController(array $extensions): array
    {
        $extensionClassesByController = [];

        foreach ($extensions as $extension) {
            $extensionClassesByController[$extension['controllerClass']][] = $extension['extensionClass'];
        }

        return $extensionClassesByController;
    }

    private function findForRole(ReflectionClass $reflectionClass): ?string
    {
        $attributes = $reflectionClass->getAttributes(ForRole::class);

        if (count($attributes) !== 0) {
            return $attributes[0]->newInstance()->role;
        }

        return null;
    }
}
