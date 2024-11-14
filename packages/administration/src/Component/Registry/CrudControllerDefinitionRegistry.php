<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Registry;

use ReflectionClass;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

final class CrudControllerDefinitionRegistry
{
    public const CRUD_CONTROLLERS_PARAMETER = 'shopsys.admin.crud_controllers';

    /**
     * @var \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem[]
     */
    private array $items = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param array $crudControllers ['class' => string, 'entityClass' => string]|null
     */
    public function __construct(
        private readonly EntityNameResolver $entityNameResolver,
        private readonly array $crudControllers,
    ) {
        foreach ($this->crudControllers as $crudController) {
            $this->addItem($crudController['class'], $crudController['entityClass']);
        }
    }

    /**
     * @param string $controllerClass
     * @param string $entityClass
     */
    private function addItem(string $controllerClass, string $entityClass): void
    {
        $resolverEntityClass = $this->entityNameResolver->resolve($entityClass);
        $shortEntityClass = (new ReflectionClass($entityClass))->getShortName();

        $item = new CrudControllerDefinitionItem(
            $controllerClass,
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
}
