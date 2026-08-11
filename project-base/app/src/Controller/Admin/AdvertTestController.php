<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Advert\Advert;

/**
 * Testing controller for the CRUD list domain control feature.
 *
 * Advert implements DomainSeparatedEntityInterface, so the domain condition
 * is applied to the list query automatically — no configureQuery() needed.
 */
#[CrudController(Advert::class)]
final class AdvertTestController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setMenuTitle(t('Adverts (domain filter test)'))
            ->setMenuSection(SideMenuBuilder::ROOT_CMS)
            ->setListDomainControl(CrudListDomainControl::QUICK_FILTER);
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('name', [
                'label' => t('Name'),
            ])
            ->add('positionName', [
                'label' => t('Area'),
            ])
            ->add('domainId', [
                'label' => t('Domain ID'),
            ]);
    }
}
