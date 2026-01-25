<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\DependencyInjection\Compiler;

use Override;
use ReflectionClass;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InitializeControllersCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $services = $container->findTaggedServiceIds('controller.service_arguments');
        $crudControllers = [];

        foreach ($services as $id => $tags) {
            $crudControllerClass = $this->processService($container, $id);

            if ($crudControllerClass !== null) {
                $crudControllers[] = $crudControllerClass;
            }
        }

        $container->setParameter(
            CrudControllerRegistry::CRUD_CONTROLLERS_PARAMETER,
            $crudControllers,
        );
    }

    /**
     * Processes a service definition for a controller to see if it's tagged with \@CrudController and if so, returns an array with class name and entity class.
     *
     * @return array{class: string, entityClass: string}|null
     */
    private function processService(ContainerBuilder $container, string $serviceId): ?array
    {
        $definition = $container->getDefinition($serviceId);
        $class = $definition->getClass();

        if (!$class) {
            return null;
        }

        $reflectionClass = new ReflectionClass($class);
        $attributeInstance = $this->getCrudControllerAttribute($reflectionClass);

        if ($attributeInstance === null) {
            return null;
        }

        if (is_subclass_of($class, AbstractCrudController::class) === false) {
            throw new RuntimeException(sprintf('Controller %s is not a subclass of %s', $class, AbstractCrudController::class));
        }

        return [
            'class' => $class,
            'entityClass' => $attributeInstance->getEntityClass(),
        ];
    }

    /**
     * Retrieves the CrudController attribute from a reflection class.
     */
    private function getCrudControllerAttribute(ReflectionClass $reflectionClass): ?CrudController
    {
        $attributes = $reflectionClass->getAttributes(CrudController::class);

        if (count($attributes) !== 0) {
            /** @var \Shopsys\AdministrationBundle\Component\Attributes\CrudController $attributeInstance */
            $attributeInstance = $attributes[0]->newInstance();

            return $attributeInstance;
        }

        return null;
    }
}
