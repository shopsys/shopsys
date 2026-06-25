<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;

/**
 * Manual test controller: non-domain entity (no domainId field, no $domains association).
 * Expected: no domain switcher above the grid, grid is not filtered (DomainFilterType::NONE).
 */
#[CrudController(Unit::class)]
final class CrudDomainNoneTestController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config->setMenuTitle(t('TEST domain filter: NONE (Unit)'));
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('id', [
                'label' => t('Id'),
            ])
            ->add('name', [
                'label' => t('Name'),
            ]);
    }
}
