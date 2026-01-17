<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Form\Admin\Customer\RoleGroup\CustomerUserRoleGroupFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SuperAdminOnly]
class CustomerUserRoleGroupController extends AdminBaseController
{
    public function __construct(
        protected readonly CustomerUserRoleGroupGridFactory $gridFactory,
        protected readonly CustomerUserRoleGroupDataFactory $customerUserRoleGroupDataFactory,
        protected readonly CustomerUserRoleGroupFacade $customerUserRoleGroupFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly Domain $domain,
    ) {
    }

    #[Route(path: '/superadmin/customer/role-group/list/', name: 'admin_superadmin_customer_user_role_group_list')]
    public function listAction(): Response
    {
        $grid = $this->gridFactory->create('ROLE_SUPERADMIN');

        return $this->render('@ShopsysAdministration/content/customer/roleGroup/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/superadmin/customer/role-group/new/', name: 'admin_superadmin_customer_user_role_group_new')]
    public function newAction(Request $request): Response
    {
        $roleGroupData = $this->customerUserRoleGroupDataFactory->create();
        $form = $this->createForm(CustomerUserRoleGroupFormType::class, $roleGroupData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerUserRoleGroup = $this->customerUserRoleGroupFacade->create($roleGroupData);

            if (!$this->domain->hasAdminAllDomainsEnabled()) {
                $this->addErrorFlash(t('Creating a record requires all domains to be enabled as domain-specific fields cannot be empty. If you want to proceed, select all domains in the Domain filter in the header first.'));

                return $this->redirectToRoute('admin_superadmin_customer_user_role_group_new');
            }

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

        return $this->render('@ShopsysAdministration/content/customer/roleGroup/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/superadmin/customer/role-group/edit/{id}', name: 'admin_superadmin_customer_user_role_group_edit', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $customerUserRoleGroup = $this->customerUserRoleGroupFacade->getById($id);
        $administratorRoleGroupData = $this->customerUserRoleGroupDataFactory->createFromCustomerUserRoleGroup($customerUserRoleGroup);

        $form = $this->createForm(CustomerUserRoleGroupFormType::class, $administratorRoleGroupData, [
            'customer_user_role_group' => $customerUserRoleGroup,
        ]);
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

        return $this->render('@ShopsysAdministration/content/customer/roleGroup/edit.html.twig', [
            'form' => $form->createView(),
            'customerUserRoleGroup' => $customerUserRoleGroup,
        ]);
    }

    #[Route(path: '/superadmin/customer/role-group/delete/{id}', name: 'admin_superadmin_customer_user_role_group_delete', requirements: ['id' => '\d+'])]
    #[CsrfProtection]
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
