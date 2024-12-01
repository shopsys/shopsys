<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Registry;

use ReflectionClass;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Webmozart\Assert\Assert;

final class CrudControllerDefinitionRegistry
{
    public const CRUD_CONTROLLERS_PARAMETER = 'shopsys.admin.crud_controllers';

    /**
     * @var \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem[]
     */
    private array $items = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param array<int, array{class: class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, entityClass: string}> $crudControllers
     */
    public function __construct(
        private readonly EntityNameResolver $entityNameResolver,
        private readonly array $crudControllers = [],
    ) {
        foreach ($this->crudControllers as $crudController) {
            $this->addItem($crudController['class'], $crudController['entityClass']);
        }
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     * @param class-string $entityClass
     */
    private function addItem(string $controllerClass, string $entityClass): void
    {
        $resolverEntityClass = $this->entityNameResolver->resolve($entityClass);
        $shortEntityClass = (new ReflectionClass($entityClass))->getShortName();
        $shortControllerClass = (new ReflectionClass($controllerClass))->getShortName();

        $item = new CrudControllerDefinitionItem(
            $controllerClass,
            $shortControllerClass,
            $resolverEntityClass,
            $shortEntityClass,
        );

        $this->items[$controllerClass] = $item;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param string $controllerClass
     * @return \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem
     */
    public function getItem(string $controllerClass): CrudControllerDefinitionItem
    {
        Assert::keyExists($this->items, $controllerClass, 'Crud controller class is not registered.');

        return $this->items[$controllerClass];
    }
}
