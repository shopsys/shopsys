<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Form\Admin\Customer\RoleGroup\CustomerUserRoleGroupFormType;
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
     */
    public function __construct(
        protected readonly CustomerUserRoleGroupGridFactory $gridFactory,
        protected readonly CustomerUserRoleGroupDataFactory $customerUserRoleGroupDataFactory,
        protected readonly CustomerUserRoleGroupFacade $customerUserRoleGroupFacade,
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
}
