<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Registry;

use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;

final class CrudControllerDefinitionItem
{
    /**
     * @param string $controllerClass
     * @param string $entityClass
     * @param string $shortEntityClass
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfigData $config
     */
    public function __construct(
        public string $controllerClass,
        public string $entityClass,
        public string $shortEntityClass,
        public CrudConfigData $config,
    ) {
    }
}
