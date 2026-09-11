<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Override;
use Shopsys\AdministrationBundle\Component\Action\RowAction;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Model\Product\Parameter\ParameterGroupCrudHandler;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ParameterGroupFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup;

#[CrudController(ParameterGroup::class)]
#[ForRole(AdminRoleConstant::ROLE_PARAMETER_GROUP)]
class ParameterGroupController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setRoutePrefix('/product')
            ->setMenuSection(
                SideMenuBuilder::ROOT_SETTING,
                SideMenuBuilder::SECTION_LISTS,
                ['after' => SideMenuBuilder::LIST_PARAMETER],
            )
            ->registerHandler(ParameterGroupCrudHandler::class);
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('name', [
                'label' => t('Name'),
            ]);

        $datagrid->enableDragAndDrop('position');

        $datagrid->actions()->update(
            'delete',
            fn (RowAction $rowAction) => $rowAction->setConfirmMessage(
                t('Do you really want to remove this parameter groups? By deleting this parameter group you will unset all groups by associated parameters. This step is irreversible!'),
            ),
        );
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
        $formConfigurator->useFormType(ParameterGroupFormType::class, [
            'parameterGroup' => $entity,
        ]);
    }
}
