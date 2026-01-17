<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

use ReflectionClass;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use SplPriorityQueue;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

final class CrudControllerRegistry
{
    public const string CRUD_CONTROLLERS_PARAMETER = 'shopsys.admin.crud_controllers';
    public const string CRUD_CONTROLLERS_EXTENSIONS_PARAMETER = 'shopsys.admin.crud_controllers_extensions';

    /**
     * @var \Shopsys\AdministrationBundle\Component\Crud\Definition[]|null
     */
    private ?array $items = null;

    /**
     * @param array<int, array{class: class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, entityClass: string}> $crudControllers
     * @param array<int, array{extensionClass: class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension>, controllerClass: class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, priority: int}> $crudControllerExtensions
     */
    public function __construct(
        private readonly EntityNameResolver $entityNameResolver,
        private readonly ContainerInterface $container,
        private array $crudControllers = [],
        private array $crudControllerExtensions = [],
    ) {
    }

    private function buildDefinitions(): void
    {
        $this->items = [];
        $extensionsByCrudController = $this->loadExtensions($this->crudControllerExtensions);

        foreach ($this->crudControllers as $crudController) {
            $controllerClass = $crudController['class'];

            $this->addItem($controllerClass, $crudController['entityClass'], $extensionsByCrudController[$controllerClass] ?? []);
        }
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     * @param class-string $entityClass
     * @param \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension[] $extensions
     */
    private function addItem(string $controllerClass, string $entityClass, array $extensions): void
    {
        $entityClass = $this->entityNameResolver->resolve($entityClass);
        $entityName = (new ReflectionClass($entityClass))->getShortName();
        $controllerName = (new ReflectionClass($controllerClass))->getShortName();

        /** @var \Shopsys\AdministrationBundle\Controller\AbstractCrudController $crudController */
        $crudController = $this->container->get($controllerClass);

        $item = new Definition(
            $controllerClass,
            $controllerName,
            $entityClass,
            $entityName,
            $this->loadCrudConfiguration($controllerClass, $entityName, $extensions),
            $extensions,
        );

        $crudController->definition = $item;

        $this->items[$controllerClass] = $item;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Crud\Definition[]
     */
    public function getItems(): array
    {
        if ($this->items === null) {
            $this->buildDefinitions();
        }

        return $this->items;
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     */
    public function getItem(string $controllerClass): Definition
    {
        $items = $this->getItems();

        Assert::keyExists($items, $controllerClass, 'CRUD controller class is not registered.');

        return $items[$controllerClass];
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     * @param \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension[] $extensions
     */
    private function loadCrudConfiguration(
        string $controllerClass,
        string $entityName,
        array $extensions,
    ): CrudConfigData {
        /** @var \Shopsys\AdministrationBundle\Controller\AbstractCrudController $crudController */
        $crudController = $this->container->get($controllerClass);

        $config = new CrudConfig($entityName);
        $crudController->configure($config);

        foreach ($extensions as $extension) {
            $extension->configure($config);
        }

        return $config->getConfig();
    }

    /**
     * @param array<int, array{extensionClass: string, controllerClass: string, priority: int}> $extensions
     * @return array<class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension[]>
     */
    private function loadExtensions(array $extensions): array
    {
        $queueByController = [];

        foreach ($extensions as $extension) {
            if (isset($queueByController[$extension['controllerClass']]) === false) {
                $queueByController[$extension['controllerClass']] = new SplPriorityQueue();
            }

            $queueByController[$extension['controllerClass']]->insert(
                $this->container->get($extension['extensionClass']),
                $extension['priority'],
            );
        }

        $extensionsByCrudController = [];

        foreach ($queueByController as $controller => $queue) {
            $queue->top();
            $queue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
            $extensionsByCrudController[$controller] = array_reverse(iterator_to_array($queue));
        }

        return $extensionsByCrudController;
    }
}
