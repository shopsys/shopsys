<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\DependencyInjection\Compiler;

use Override;
use ReflectionClass;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Attributes\CrudControllerExtension;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LoadControllersExtensionCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $services = $container->findTaggedServiceIds('shopsys.admin.crud_controller_extension');

        $extensions = [];

        foreach ($services as $id => $tags) {
            $crudControllerExtension = $this->processService($container, $id);

            if ($crudControllerExtension !== null) {
                $extensions[] = $crudControllerExtension;
            }
        }

        $container->setParameter(
            CrudControllerRegistry::CRUD_CONTROLLERS_EXTENSIONS_PARAMETER,
            $extensions,
        );
    }

    /**
     * Processes a service definition for a controller extensions
     *
     * @return array{extensionClass: string, controllerClass: string, priority: int}|null
     */
    protected function processService(ContainerBuilder $container, string $id): ?array
    {
        $definition = $container->getDefinition($id);
        $class = $definition->getClass();

        if (!$class) {
            return null;
        }

        $reflectionClass = new ReflectionClass($class);
        $attributeInstance = $this->getCrudControllerExtensionAttribute($reflectionClass);

        if ($attributeInstance === null) {
            return null;
        }

        if (is_subclass_of($class, AbstractCrudControllerExtension::class) === false) {
            throw new RuntimeException(sprintf('Controller %s is not a subclass of %s', $class, AbstractCrudControllerExtension::class));
        }

        $priority = $attributeInstance->getPriority();

        // Increase priority for App\* classes to make sure they are loaded last
        if (str_starts_with($class, 'App\\') && $priority === 0) {
            $priority = 1000;
        }

        return [
            'extensionClass' => $class,
            'controllerClass' => $attributeInstance->getCrudController(),
            'priority' => $priority,
        ];
    }

    /**
     * Retrieves the CrudControllerExtension attribute from a reflection class.
     */
    protected function getCrudControllerExtensionAttribute(ReflectionClass $reflectionClass): ?CrudControllerExtension
    {
        $attributes = $reflectionClass->getAttributes(CrudControllerExtension::class);

        if (count($attributes) !== 0) {
            /** @var \Shopsys\AdministrationBundle\Component\Attributes\CrudControllerExtension $attributeInstance */
            $attributeInstance = $attributes[0]->newInstance();

            return $attributeInstance;
        }

        return null;
    }
}
