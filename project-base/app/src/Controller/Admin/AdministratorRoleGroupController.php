<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\AdministratorRoleGroupFormType;
use App\Model\Administrator\AdministratorFacade;
use App\Model\Administrator\RoleGroup\AdministratorRoleGroupData;
use App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;
use App\Model\Administrator\RoleGroup\Exception\AdministratorRoleGroupNotFoundException;
use App\Model\Administrator\RoleGroup\Exception\DuplicateNameException;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdministratorRoleGroupController extends AdminBaseController
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
     * @var \App\Model\Administrator\AdministratorFacade
     */
    private AdministratorFacade $administratorFacade;

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade $administratorRoleGroupFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \App\Model\Administrator\AdministratorFacade $administratorFacade
     */
    public function __construct(
        AdministratorRoleGroupFacade $administratorRoleGroupFacade,
        GridFactory $gridFactory,
        BreadcrumbOverrider $breadcrumbOverrider,
        AdministratorFacade $administratorFacade
    ) {
        $this->administratorRoleGroupFacade = $administratorRoleGroupFacade;
        $this->gridFactory = $gridFactory;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
        $this->administratorFacade = $administratorFacade;
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
        $grid->addEditActionColumn('admin_administratorrolegroup_edit', ['id' => 'arg.id']);
        $grid->addActionColumn('document-copy', 'Copy', 'admin_administratorrolegroup_copy', ['id' => 'arg.id']);
        $grid->addDeleteActionColumn('admin_administratorrolegroup_delete', ['id' => 'arg.id'])
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
            try {
                $administratorRoleGroup = $this->administratorRoleGroupFacade->create($roleGroupData);

                $this->addSuccessFlashTwig(
                    t('Administrator role group <strong><a href="{{ url }}">{{ name }}</a></strong> was created'),
                    [
                        'name' => $administratorRoleGroup->getName(),
                        'url' => $this->generateUrl('admin_administratorrolegroup_edit', ['id' => $administratorRoleGroup->getId()]),
                    ]
                );

                return $this->redirectToRoute('admin_administratorrolegroup_list');
            } catch (DuplicateNameException $ex) {
                $this->addErrorFlashTwig(
                    t('Role group name <strong>{{ name }}</strong> is already used'),
                    [
                        'name' => $roleGroupData->name,
                    ]
                );
            }
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
            try {
                $this->administratorRoleGroupFacade->edit($administratorRoleGroup, $administratorRoleGroupData);

                $this->addSuccessFlashTwig(
                    t('Administrator role group <strong><a href="{{ url }}">{{ name }}</a></strong> was edited'),
                    [
                        'name' => $administratorRoleGroupData->name,
                        'url' => $this->generateUrl('admin_administratorrolegroup_edit', ['id' => $id]),
                    ]
                );

                return $this->redirectToRoute('admin_administratorrolegroup_list');
            } catch (DuplicateNameException $ex) {
                $this->addErrorFlashTwig(
                    t('Role group name <strong>{{ name }}</strong> is already used'),
                    [
                        'name' => $administratorRoleGroupData->name,
                    ]
                );
            }
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
     * @Route("/administrator/groups/copy/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function copyAction(Request $request, int $id): Response
    {
        try {
            $administratorRoleGroup = $this->administratorRoleGroupFacade->getById($id);
            $administratorRoleGroupData = new AdministratorRoleGroupData();
            $administratorRoleGroupData->fillFromEntity($administratorRoleGroup);

            $form = $this->createForm(AdministratorRoleGroupFormType::class, $administratorRoleGroupData, [
                'action' => $this->generateUrl('admin_administratorrolegroup_new'),
            ]);
            $form->handleRequest($request);

            $this->breadcrumbOverrider->overrideLastItem(t('New administrator role group'));

            return $this->render('/Admin/Content/Administrator/RoleGroup/new.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (AdministratorRoleGroupNotFoundException $ex) {
            $this->addErrorFlash(t('Selected administrator role group doesn\'t exist.'));

            return $this->redirectToRoute('admin_administratorrolegroup_list');
        }
    }

    /**
     * @Route("/administrator/groups/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id): Response
    {
        $namesUsingThisRoleGroup = $this->administratorFacade->findAdministratorNamesWithRoleGroup($id);

        if (count($namesUsingThisRoleGroup) !== 0) {
            $this->addErrorFlashTwig(
                t('Role group cannot be deleted, because some administrators are using it: {{ names }}'),
                [
                    'names' => implode(', ', $namesUsingThisRoleGroup),
                ]
            );

            return $this->redirectToRoute('admin_administratorrolegroup_list');
        }

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

        return $this->redirectToRoute('admin_administratorrolegroup_list');
    }
}
