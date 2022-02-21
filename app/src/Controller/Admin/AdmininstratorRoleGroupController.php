<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\Routing\Annotation\Route;

class AdmininstratorRoleGroupController extends AdminBaseController
{
    /**
     * @var \App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade
     */
    private AdministratorRoleGroupFacade $administratorRoleGroupFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private GridFactory $gridFactory;

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade $administratorRoleGroupFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     */
    public function __construct(AdministratorRoleGroupFacade $administratorRoleGroupFacade, GridFactory $gridFactory)
    {
        $this->administratorRoleGroupFacade = $administratorRoleGroupFacade;
        $this->gridFactory = $gridFactory;
    }

    /**
     * @Route("/administrator/groups/list/")
     */
    public function listAction()
    {
        $queryBuilder = $this->administratorRoleGroupFacade->getAllQueryBuilder();
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'arg.id');

        $grid = $this->gridFactory->create('administratorRoleGroupsList', $dataSource);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'arg.name', t('Role name'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        //$grid->addEditActionColumn('admin_administrator_edit', ['id' => 'a.id']);
        //$grid->addDeleteActionColumn('admin_administrator_delete', ['id' => 'a.id'])
        //    ->setConfirmMessage(t('Do you really want to remove this administrator?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Administrator/listGrid.html.twig');

        return $this->render('Admin/Content/Administrator/RoleGroup/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }
}
