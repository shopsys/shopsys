<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Order\Order;
use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;

/**
 * Manual test controller: entity with a scalar `domainId` field.
 * Expected: global domain switcher above the grid (DomainFilterMode::SWITCH default),
 * grid filtered by `o.domainId = <selected domain>` (DomainFilterType::SCALAR).
 */
#[CrudController(Order::class)]
final class CrudDomainScalarTestController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config->setMenuTitle(t('TEST domain filter: SCALAR + SWITCH (Order)'));
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('id', [
                'label' => t('Id'),
            ])
            ->add('number', [
                'label' => t('Number'),
            ])
            ->add('domainId', [
                'label' => t('Domain'),
            ])
            ->add('createdAt', [
                'label' => t('Created at'),
            ]);
    }
}
