<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Model\Navigation\NavigationItemCrudHandler;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Form\Admin\Navigation\NavigationItemFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItem;

#[CrudController(NavigationItem::class)]
#[ForRole(AdminRoleConstant::ROLE_NAVIGATION)]
class NavigationController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setEntityNamePlural(t('Navigation'))
            ->setMenuSection(SideMenuBuilder::ROOT_CMS, null, ['after' => SideMenuBuilder::SECTION_BLOG])
            ->setListDomainControl(CrudListDomainControl::SWITCHER)
            ->registerHandler(NavigationItemCrudHandler::class);
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('name', [
                'label' => t('Name'),
            ]);

        $datagrid->enableDragAndDrop('position');
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?Presentable $entity = null): void
    {
        $formConfigurator->useFormType(NavigationItemFormType::class, [
            'navigationItem' => $entity,
        ]);
    }
}
