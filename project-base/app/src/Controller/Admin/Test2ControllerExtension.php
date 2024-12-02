<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudControllerExtension;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;

#[CrudControllerExtension(crudController: TestController::class, priority: 20)]
class Test2ControllerExtension extends AbstractCrudControllerExtension
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid $datagrid
     */
    #[Override]
    public function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid->add('city', [
            'label' => 'City',
        ]);
    }
}
