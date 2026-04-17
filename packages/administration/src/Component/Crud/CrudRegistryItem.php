<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;

final readonly class CrudRegistryItem
{
    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     * @param class-string $entityClass
     */
    public function __construct(
        public string $controllerClass,
        public string $controllerName,
        public string $entityClass,
        public string $entityName,
        public CrudConfigData $config,
    ) {
    }

    public function getRoleConstant(): string
    {
        return CrudTransformationHelper::generateRoleConstant($this->controllerName, $this->config->getCustomRoleConstant());
    }
}
