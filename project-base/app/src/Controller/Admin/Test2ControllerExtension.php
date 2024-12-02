<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\AdministrationBundle\Component\Attributes\CrudControllerExtension;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;

#[CrudControllerExtension(crudController: TestController::class, priority: 20)]
class Test2ControllerExtension extends AbstractCrudControllerExtension
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid $datagrid
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid
     */
    public function configureDatagrid(Datagrid $datagrid): Datagrid
    {
        $datagrid->add('city', [
            'label' => 'City',
        ]);

        return $datagrid;
    }
}
