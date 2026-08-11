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
use Shopsys\FrameworkBundle\Model\Slider\SliderItem;

/**
 * Testing controller for the CRUD list domain control feature.
 *
 * Uses the SWITCHER variant — the global administration domain switcher in the header
 * drives the list. There is no "All domains" option and $allowedDomainIds is not
 * supported (passing it throws InvalidArgumentException).
 *
 * SliderItem implements DomainSeparatedEntityInterface, so the domain condition
 * is applied to the list query automatically.
 */
#[CrudController(SliderItem::class)]
final class SliderItemTestController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setMenuTitle(t('Slider items (domain switcher test)'))
            ->setMenuSection(SideMenuBuilder::ROOT_CMS)
            ->setListDomainControl(CrudListDomainControl::SWITCHER);
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('name', [
                'label' => t('Name'),
            ])
            ->add('link', [
                'label' => t('Link'),
            ])
            ->add('domainId', [
                'label' => t('Domain ID'),
            ]);
    }
}
