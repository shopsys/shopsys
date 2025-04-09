<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Registry;

final class CrudControllerDefinitionItem
{
    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     * @param string $controllerName
     * @param class-string $entityClass
     * @param string $shortEntityClass
     */
    public function __construct(
        public string $controllerClass,
        public string $controllerName,
        public string $entityClass,
        public string $shortEntityClass,
    ) {
    }
}
