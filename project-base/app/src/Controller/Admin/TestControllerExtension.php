<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\AdministrationBundle\Component\Attributes\CrudControllerExtension;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;

#[CrudControllerExtension(crudController: TestController::class, priority: 15)]
class TestControllerExtension extends AbstractCrudControllerExtension
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid $datagrid
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid
     */
    public function configureDatagrid(Datagrid $datagrid): Datagrid
    {
        $datagrid->remove('number');

        $datagrid->rowActions()->reorder(['delete', 'link']);

        return $datagrid;
    }
}
