<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Model\Slider\SliderItemCrudHandler;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Form\Admin\Slider\SliderItemFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem;

#[CrudController(SliderItem::class)]
#[ForRole(AdminRoleConstant::ROLE_SLIDER_ITEM)]
class SliderController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setEntityNameSingular(t('Banner'))
            ->setEntityNamePlural(t('Banners'))
            ->setMenuSection(SideMenuBuilder::ROOT_CMS, SideMenuBuilder::SECTION_HOMEPAGE, 'first')
            ->setListDomainControl(CrudListDomainControl::SWITCHER)
            ->registerHandler(SliderItemCrudHandler::class);
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
            ]);

        $datagrid->enableDragAndDrop('position');
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?Presentable $entity = null): void
    {
        $formConfigurator->useFormType(SliderItemFormType::class, [
            'scenario' => $entity === null ? SliderItemFormType::SCENARIO_CREATE : SliderItemFormType::SCENARIO_EDIT,
            'slider_item' => $entity,
        ]);
    }
}
