<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Customer\RoleGroup\CustomerUserRoleGroupFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerUserRoleGroupController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupGridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupDataFactory $customerUserRoleGroupDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade $customerUserRoleGroupFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        protected readonly CustomerUserRoleGroupGridFactory $gridFactory,
        protected readonly CustomerUserRoleGroupDataFactory $customerUserRoleGroupDataFactory,
        protected readonly CustomerUserRoleGroupFacade $customerUserRoleGroupFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/customer/role-group/list/', name: 'admin_superadmin_customer_user_role_group_list')]
    public function listAction(): Response
    {
        $grid = $this->gridFactory->create();

        return $this->render('@ShopsysFramework/Admin/Content/Customer/RoleGroup/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/customer/role-group/new/', name: 'admin_superadmin_customer_user_role_group_new')]
    public function newAction(Request $request): Response
    {
        $roleGroupData = $this->customerUserRoleGroupDataFactory->create();
        $form = $this->createForm(CustomerUserRoleGroupFormType::class, $roleGroupData, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerUserRoleGroup = $this->customerUserRoleGroupFacade->create($roleGroupData);

            $this->addSuccessFlashTwig(
                t('Customer user role group <strong><a href="{{ url }}">{{ name }}</a></strong> was created'),
                [
                    'name' => $customerUserRoleGroup->getName(),
                    'url' => $this->generateUrl('admin_superadmin_customer_user_role_group_edit', ['id' => $customerUserRoleGroup->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_superadmin_customer_user_role_group_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Customer/RoleGroup/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/customer/role-group/edit/{id}', name: 'admin_superadmin_customer_user_role_group_edit', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $customerUserRoleGroup = $this->customerUserRoleGroupFacade->getById($id);
        $administratorRoleGroupData = $this->customerUserRoleGroupDataFactory->createFromCustomerUserRoleGroup($customerUserRoleGroup);

        $form = $this->createForm(CustomerUserRoleGroupFormType::class, $administratorRoleGroupData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerUserRoleGroup = $this->customerUserRoleGroupFacade->edit($customerUserRoleGroup->getId(), $administratorRoleGroupData);

            $this->addSuccessFlashTwig(
                t('Customer user role group <strong><a href="{{ url }}">{{ name }}</a></strong> was edited'),
                [
                    'name' => $customerUserRoleGroup->getName(),
                    'url' => $this->generateUrl('admin_superadmin_customer_user_role_group_edit', ['id' => $id]),
                ],
            );

            return $this->redirectToRoute('admin_superadmin_customer_user_role_group_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing customer user role group - %name%', ['%name%' => $customerUserRoleGroup->getName()]),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Customer/RoleGroup/edit.html.twig', [
            'form' => $form->createView(),
            'customerUserRoleGroup' => $customerUserRoleGroup,
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/customer/role-group/delete/{id}', name: 'admin_superadmin_customer_user_role_group_delete', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): Response
    {
        $customerUserRoleGroup = $this->customerUserRoleGroupFacade->getById($id);
        $customerUserRoleGroupName = $customerUserRoleGroup->getName();
        $customerUserCount = $this->customerUserRoleGroupFacade->getCustomerUserCountByRoleGroup($customerUserRoleGroup->getId());

        if ($customerUserCount !== 0) {
            $this->addErrorFlashTwig(
                t('Role group <strong>{{ roleGroupName }}</strong> cannot be deleted, because some customer users are using it'),
                [
                    'roleGroupName' => $customerUserRoleGroupName,
                ],
            );

            return $this->redirectToRoute('admin_superadmin_customer_user_role_group_list');
        }

        $this->customerUserRoleGroupFacade->delete($id);
        $this->addSuccessFlashTwig(
            t('Customer user role group <strong>{{ name }}</strong> deleted.'),
            [
                'name' => $customerUserRoleGroupName,
            ],
        );

        return $this->redirectToRoute('admin_superadmin_customer_user_role_group_list');
    }
}
