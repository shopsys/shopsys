<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;

final readonly class Definition
{
    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     * @param class-string $entityClass
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

    public function getConfig(): CrudConfigData
    {
        return $this->config;
    }

    public function getRoleConstant(): string
    {
        return $this->getConfig()->getCustomRoleConstant() ?? 'ROLE_CRUD_' . strtoupper(CrudTransformationHelper::transformToRouteName($this->controllerName));
    }
}
