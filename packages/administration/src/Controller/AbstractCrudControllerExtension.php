<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;

abstract class AbstractCrudControllerExtension
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfig $config
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfig
     */
    public function configure(CrudConfig $config): CrudConfig
    {
        return $config;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig $actions
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig
     */
    public function configureActions(ActionsConfig $actions): ActionsConfig
    {
        return $actions;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid $datagrid
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid
     */
    public function configureDatagrid(Datagrid $datagrid): Datagrid
    {
        return $datagrid;
    }
}
