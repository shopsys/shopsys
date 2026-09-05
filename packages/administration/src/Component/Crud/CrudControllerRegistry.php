<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

use ReflectionClass;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Webmozart\Assert\Assert;

final class CrudControllerRegistry
{
    public const string CRUD_CONTROLLERS_PARAMETER = 'shopsys.admin.crud_controllers';
    public const string CRUD_CONTROLLERS_EXTENSIONS_PARAMETER = 'shopsys.admin.crud_controllers_extensions';

    /**
     * @var array<class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, array{controllerName: string, entityClass: class-string, entityName: string}>|null
     */
    private ?array $resolvedMetadata = null;

    /**
     * @var array<class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension[]>|null
     */
    private ?array $resolvedExtensions = null;

    /**
     * @var array<class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, \Shopsys\AdministrationBundle\Component\Crud\Definition>
     */
    private array $definitions = [];

    /**
     * @param array<int, array{class: class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, entityClass: string}> $crudControllers
     * @param array<int, array{extensionClass: class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension>, controllerClass: class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, priority: int}> $crudControllerExtensions sorted by ascending priority
     */
    public function __construct(
        private readonly EntityNameResolver $entityNameResolver,
        private readonly CrudRoleConstantProvider $crudRoleConstantProvider,
        #[TaggedLocator('shopsys.admin.crud_controllers')]
        private readonly ServiceLocator $controllers,
        #[TaggedLocator('shopsys.admin.crud_handler')]
        private readonly ServiceLocator $handlers,
        private readonly array $crudControllers = [],
        private readonly array $crudControllerExtensions = [],
    ) {
    }

    /**
     * Returns all registered CRUD controllers with metadata and config (including disabled).
     *
     * @return \Shopsys\AdministrationBundle\Component\Crud\CrudRegistryItem[]
     */
    public function getAll(): array
    {
        $items = [];

        foreach ($this->getResolvedMetadata() as $controllerClass => $meta) {
            $items[] = new CrudRegistryItem(
                controllerClass: $controllerClass,
                controllerName: $meta['controllerName'],
                entityClass: $meta['entityClass'],
                entityName: $meta['entityName'],
                config: $this->buildConfig($controllerClass),
            );
        }

        return $items;
    }

    /**
     * Returns full Definition for a specific controller (with handlers, extensions).
     * Definitions are cached per controller for the lifetime of the request.
     *
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     */
    public function getDefinition(string $controllerClass): Definition
    {
        if (!isset($this->definitions[$controllerClass])) {
            $metadata = $this->getResolvedMetadata();

            Assert::keyExists($metadata, $controllerClass, 'CRUD controller class is not registered.');

            $meta = $metadata[$controllerClass];
            $config = $this->buildConfig($controllerClass);
            $extensions = $this->getResolvedExtensions()[$controllerClass] ?? [];

            $this->definitions[$controllerClass] = new Definition(
                $controllerClass,
                $meta['controllerName'],
                $meta['entityClass'],
                $meta['entityName'],
                $config,
                $extensions,
                $this->loadHandlers($config->getHandlerClasses()),
            );
        }

        return $this->definitions[$controllerClass];
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     */
    private function buildConfig(string $controllerClass): CrudConfigData
    {
        $meta = $this->getResolvedMetadata()[$controllerClass];
        $extensions = $this->getResolvedExtensions()[$controllerClass] ?? [];

        /** @var \Shopsys\AdministrationBundle\Controller\AbstractCrudController $crudController */
        $crudController = $this->controllers->get($controllerClass);

        $config = new CrudConfig($meta['entityName'], $this->crudRoleConstantProvider->findCustomRoleConstant($controllerClass));
        $crudController->configure($config);

        foreach ($extensions as $extension) {
            $extension->configure($config);
        }

        return $config->getConfig();
    }

    /**
     * @return array<class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, array{controllerName: string, entityClass: class-string, entityName: string}>
     */
    private function getResolvedMetadata(): array
    {
        if ($this->resolvedMetadata === null) {
            $this->resolvedMetadata = [];

            foreach ($this->crudControllers as $crudController) {
                $controllerClass = $crudController['class'];
                $entityClass = $this->entityNameResolver->resolve($crudController['entityClass']);

                $this->resolvedMetadata[$controllerClass] = [
                    'controllerName' => (new ReflectionClass($controllerClass))->getShortName(),
                    'entityClass' => $entityClass,
                    'entityName' => (new ReflectionClass($entityClass))->getShortName(),
                ];
            }
        }

        return $this->resolvedMetadata;
    }

    /**
     * @return array<class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension[]>
     */
    private function getResolvedExtensions(): array
    {
        if ($this->resolvedExtensions === null) {
            $this->resolvedExtensions = [];

            foreach ($this->crudControllerExtensions as $extension) {
                $this->resolvedExtensions[$extension['controllerClass']][] = $this->controllers->get($extension['extensionClass']);
            }
        }

        return $this->resolvedExtensions;
    }

    /**
     * @param array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, class-string<\Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface>> $handlerClasses
     * @return array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, \Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface|null>
     */
    private function loadHandlers(array $handlerClasses): array
    {
        $handlersByActionType = [];

        foreach ($handlerClasses as $actionType => $handlerClass) {
            if ($handlerClass === null) {
                $handlersByActionType[$actionType] = null;

                continue;
            }

            if (!$this->handlers->has($handlerClass)) {
                throw new RuntimeException(sprintf(
                    'Handler "%s" for action "%s" is not registered in the service container.',
                    $handlerClass,
                    $actionType,
                ));
            }

            $handlersByActionType[$actionType] = $this->handlers->get($handlerClass);
        }

        return $handlersByActionType;
    }
}
