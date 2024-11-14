<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Registry;

final class CrudControllerDefinitionItem
{
    /**
     * @param string $controllerClass
     * @param string $entityClass
     * @param string $shortEntityClass
     */
    public function __construct(
        public string $controllerClass,
        public string $entityClass,
        public string $shortEntityClass,
    ) {
    }
}
