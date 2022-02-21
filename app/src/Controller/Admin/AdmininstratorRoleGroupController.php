<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\AdministratorRoleGroupFormType;
use App\Model\Administrator\RoleGroup\AdministratorRoleGroupData;
use App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;
use App\Model\Administrator\RoleGroup\Exception\AdministratorRoleGroupNotFoundException;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @var \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider
     */
    private BreadcrumbOverrider $breadcrumbOverrider;

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade $administratorRoleGroupFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        AdministratorRoleGroupFacade $administratorRoleGroupFacade,
        GridFactory $gridFactory,
        BreadcrumbOverrider $breadcrumbOverrider
    ) {
        $this->administratorRoleGroupFacade = $administratorRoleGroupFacade;
        $this->gridFactory = $gridFactory;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
    }

    /**
     * @Route("/administrator/groups/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $queryBuilder = $this->administratorRoleGroupFacade->getAllQueryBuilder();
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'arg.id');

        $grid = $this->gridFactory->create('administratorRoleGroupsList', $dataSource);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'arg.name', t('Role name'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_admininstratorrolegroup_edit', ['id' => 'arg.id']);
        $grid->addDeleteActionColumn('admin_admininstratorrolegroup_delete', ['id' => 'arg.id'])
            ->setConfirmMessage(t('Do you really want to remove this administrator role group?'));

        return $this->render('Admin/Content/Administrator/RoleGroup/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/administrator/groups/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $roleGroupData = new AdministratorRoleGroupData();
        $form = $this->createForm(AdministratorRoleGroupFormType::class, $roleGroupData, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $administratorRoleGroup = $this->administratorRoleGroupFacade->create($roleGroupData);

            $this->addSuccessFlashTwig(
                t('Administrator role group <strong><a href="{{ url }}">{{ name }}</a></strong> was created'),
                [
                    'name' => $administratorRoleGroup->getName(),
                    'url' => $this->generateUrl('admin_admininstratorrolegroup_edit', ['id' => $administratorRoleGroup->getId()]),
                ]
            );
            return $this->redirectToRoute('admin_admininstratorrolegroup_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('/Admin/Content/Administrator/RoleGroup/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/administrator/groups/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $administratorRoleGroup = $this->administratorRoleGroupFacade->getById($id);
        $administratorRoleGroupData = new AdministratorRoleGroupData();
        $administratorRoleGroupData->fillFromEntity($administratorRoleGroup);

        $form = $this->createForm(AdministratorRoleGroupFormType::class, $administratorRoleGroupData, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->administratorRoleGroupFacade->edit($administratorRoleGroup, $administratorRoleGroupData);

            $this->addSuccessFlashTwig(
                t('Administrator role group <strong><a href="{{ url }}">{{ name }}</a></strong> was edited'),
                [
                    'name' => $administratorRoleGroupData->name,
                    'url' => $this->generateUrl('admin_admininstratorrolegroup_edit', ['id' => $id]),
                ]
            );
            return $this->redirectToRoute('admin_admininstratorrolegroup_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing administrator role group - %name%', ['%name%' => $administratorRoleGroup->getName()])
        );

        return $this->render('/Admin/Content/Administrator/RoleGroup/edit.html.twig', [
            'form' => $form->createView(),
            'administratorRoleGroup' => $administratorRoleGroup,
        ]);
    }

    /**
     * @Route("/administrator/groups/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id): Response
    {
        try {
            $name = $this->administratorRoleGroupFacade->getById($id)->getName();

            $this->administratorRoleGroupFacade->delete($id);
            $this->addSuccessFlashTwig(
                t('Administrator role group <strong>{{ name }}</strong> deleted.'),
                [
                    'name' => $name,
                ]
            );
        } catch (AdministratorRoleGroupNotFoundException $ex) {
            $this->addErrorFlash(t('Selected administrator role group doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_admininstratorrolegroup_list');
    }
}
