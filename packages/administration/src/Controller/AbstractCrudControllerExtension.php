<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;

abstract class AbstractCrudControllerExtension
{
    public function configure(CrudConfig $config): void
    {
    }

    public function configureActions(ActionsConfig $actions): void
    {
    }

    public function configureDatagrid(Datagrid $datagrid): void
    {
    }

    public function configureQuery(QueryBuilder $queryBuilder): void
    {
    }
}
