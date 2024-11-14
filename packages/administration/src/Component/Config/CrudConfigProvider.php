<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class CrudConfigProvider
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(
        public readonly ContainerInterface $container,
    ) {
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem $item
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfigData
     */
    public function getConfig(CrudControllerDefinitionItem $item): CrudConfigData
    {
        /** @var \Shopsys\AdministrationBundle\Controller\AbstractCrudController $crudController */
        $crudController = $this->container->get($item->controllerClass);

        return $crudController->getConfig();
    }
}