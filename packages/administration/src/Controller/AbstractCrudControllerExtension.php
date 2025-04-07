<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;

abstract class AbstractCrudControllerExtension
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfig $config
     */
    public function configure(CrudConfig $config): void
    {
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionsConfig $actions
     */
    public function configureActions(ActionsConfig $actions): void
    {
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid $datagrid
     */
    public function configureDatagrid(Datagrid $datagrid): void
    {
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    public function configureQuery(QueryBuilder $queryBuilder): void
    {
    }
}
