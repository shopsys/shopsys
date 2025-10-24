<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;

final readonly class Definition
{
    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     * @param string $controllerName
     * @param class-string $entityClass
     * @param string $entityName
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfigData $config
     * @param \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension[] $extensions
     */
    public function __construct(
        public string $controllerClass,
        public string $controllerName,
        public string $entityClass,
        public string $entityName,
        private CrudConfigData $config,
        private array $extensions,
    ) {
    }

    /**
     * @return \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfigData
     */
    public function getConfig(): CrudConfigData
    {
        return $this->config;
    }
}
