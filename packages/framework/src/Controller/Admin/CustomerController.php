<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\ActionColumn;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\MoneyConvertingDataSourceDecoratorFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType;
use Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserUpdateFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserListAdminFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Watchdog\WatchdogFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_CUSTOMER)]
class CustomerController extends AdminBaseController
{
    public function __construct(
        protected readonly CustomerUserDataFactory $customerUserDataFactory,
        protected readonly CustomerUserListAdminFacade $customerUserListAdminFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly OrderFacade $orderFacade,
        protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        protected readonly Domain $domain,
        protected readonly CustomerFacade $customerFacade,
        protected readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
        protected readonly WatchdogFacade $watchdogFacade,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
        protected readonly MoneyConvertingDataSourceDecoratorFactory $moneyConvertingDataSourceDecoratorFactory,
    ) {
    }

    #[Route(path: '/customer/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById($id);
        $customer = $customerUser->getCustomer();

        if ($this->customerFacade->isB2bFeaturesEnabledByCustomer($customer)) {
            return $this->redirectToRoute('admin_customer_user_edit', ['id' => $id]);
        }
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);

        $form = $this->createForm(CustomerUserUpdateFormType::class, $customerUserUpdateData, [
            'customerUser' => $customerUser,
            'domain_id' => $this->adminDomainTabsFacade->getSelectedDomainId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->customerUserFacade->editByAdmin($customerUser->getId(), $customerUserUpdateData);

            $this->addSuccessFlashTwig(
                t('Customer <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $customerUser->getFullName(),
                    'url' => $this->generateUrl('admin_customer_edit', ['id' => $customerUser->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_customer_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing customer - %name%', ['%name%' => $customerUser->getFullName()]),
        );

        $orders = $this->orderFacade->getCustomerUserOrderList($customerUser);

        return $this->render('@ShopsysAdministration/content/customer/edit.html.twig', [
            'form' => $form->createView(),
            'customerUser' => $customerUser,
            'orders' => $orders,
        ]);
    }

    #[Route(path: '/customer/edit-customer-user/{id}', name: 'admin_customer_user_edit', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editCustomerUserAction(Request $request, int $id): Response
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById($id);
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);

        $form = $this->createForm(CustomerUserFormType::class, $customerUserUpdateData->customerUserData, [
            'customerUser' => $customerUser,
            'domain_id' => $this->adminDomainTabsFacade->getSelectedDomainId(),
            'back_url' => $this->resolveBackUrl($customerUser->getCustomer()),
            'back_url_text' => $this->resolveBackUrlText($customerUser->getCustomer()),
            'renderSaveButton' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->customerUserFacade->editByAdmin($customerUser->getId(), $customerUserUpdateData);

            $this->addSuccessFlashTwig(
                t('Customer <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $customerUser->getCustomerUserFullName(),
                    'url' => $this->generateUrl('admin_customer_user_edit', ['id' => $customerUser->getId()]),
                ],
            );

            $billingAddress = $customerUser->getCustomer()->getBillingAddress();

            return $this->redirectToRoute('admin_billing_address_edit', ['id' => $billingAddress->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing customer - %name%', ['%name%' => $customerUser->getCustomerUserFullName()]),
        );

        $orders = $this->orderFacade->getCustomerUserOrderList($customerUser);

        return $this->render('@ShopsysAdministration/content/customer/user/edit.html.twig', [
            'form' => $form->createView(),
            'customerUser' => $customerUser,
            'orders' => $orders,
        ]);
    }

    #[Route(path: '/customer/list/')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->customerUserListAdminFacade->getCustomerUserListQueryBuilderByQuickSearchData(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $quickSearchForm->getData(),
        );

        $innerDataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
            $queryBuilder,
            'id',
            $this->manipulateRow(...),
            null,
        );
        $dataSource = $this->moneyConvertingDataSourceDecoratorFactory->create($innerDataSource, ['ordersSumPrice']);

        $grid = $this->gridFactory->create('customerList', $dataSource, AdminRoleConstant::ROLE_CUSTOMER);
        $grid->enablePaging();
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'name', t('Full name'), true)->setClassAttribute('text-nowrap');
        $grid->addColumn('email', 'email', t('Email'), true);
        $grid->addColumn('telephone', 'telephone', t('Telephone'), true);
        $grid->addColumn('city', 'city', t('City'), true);
        $grid->addColumn('pricingGroup', 'pricingGroup', t('Pricing group'), true);
        $grid->addColumn('orders_count', 'ordersCount', t('Number of orders'), true)->setClassAttribute('text-end');
        $grid->addColumn('orders_sum_price', 'ordersSumPrice', t('Orders value'), true)
            ->setClassAttribute('text-end');
        $grid->addColumn('last_order_at', 'lastOrderAt', t('Last order'), true)
            ->setClassAttribute('text-end');
        $grid->addColumn('isActivated', 'isActivated', t('Active'), true);

        $grid->addEditActionColumn('admin_customer_edit', ['id' => 'id']);
        $grid->addDeleteActionColumn('admin_customer_delete', ['id' => 'id'])
            ->setConfirmMessage(t('Do you really want to remove this customer?'));
        $grid->addActionColumn(ActionColumn::TYPE_RESET_PASSWORD, t('Send reset password'), 'admin_customer_send_reset_password', ['id' => 'cu.id'])
            ->setConfirmMessage(t('This will send an email to customer user for resetting password. Do you really want to send it ?'));

        $grid->setTheme('@ShopsysAdministration/content/customer/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysAdministration/content/customer/list.html.twig', [
            'gridView' => $grid->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    #[Route(path: '/customer/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $customerUserData = $this->customerUserDataFactory->createForDomainId($selectedDomainId);
        $customerUserUpdateData->customerUserData = $customerUserData;

        $form = $this->createForm(CustomerUserUpdateFormType::class, $customerUserUpdateData, [
            'customerUser' => null,
            'domain_id' => $selectedDomainId,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerUser = $this->customerUserFacade->createWithActivationMail($customerUserUpdateData);

            $this->addSuccessFlashTwig(
                t('Customer <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $customerUser->getFullName(),
                    'url' => $this->generateUrl('admin_customer_edit', ['id' => $customerUser->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_customer_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/customer/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/customer/new-customer-user/{customerId}/', name: 'admin_customer_new_customer_user', requirements: ['customerId' => '\d+'])]
    #[CanCreate]
    public function newCustomerUserAction(Request $request, int $customerId): Response
    {
        $customer = $this->customerFacade->getById($customerId);
        $customerUserData = $this->customerUserDataFactory->createForCustomerWithPresetPricingGroup($customer);

        $form = $this->createForm(CustomerUserFormType::class, $customerUserData, [
            'customerUser' => null,
            'domain_id' => $customer->getDomainId(),
            'renderSaveButton' => true,
            'allowEditSystemData' => false,
            'back_url' => $this->resolveBackUrl($customer),
            'back_url_text' => $this->resolveBackUrlText($customer),
        ]);
        $form->handleRequest($request);

        $billingAddress = $customer->getBillingAddress();

        if ($form->isSubmitted() && $form->isValid()) {
            $customerUser = $this->customerUserFacade->createCustomerUserWithActivationMail($customer, $customerUserData);
            $this->addSuccessFlashTwig(
                t('Customer <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $customerUser->getCustomerUserFullName(),
                    'url' => $this->generateUrl('admin_customer_user_edit', ['id' => $customerUser->getId()]),
                ],
            );


            return $this->redirectToRoute('admin_billing_address_edit', ['id' => $billingAddress->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Add new customer user to %companyName%', ['%companyName%' => $billingAddress->getCompanyName()]),
        );

        return $this->render('@ShopsysAdministration/content/customer/user/new.html.twig', [
            'form' => $form->createView(),
            'billingAddress' => $billingAddress,
        ]);
    }

    #[Route(path: '/customer/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById($id);
        $customer = $customerUser->getCustomer();

        try {
            $fullName = $customerUser->getCustomerUserFullName();
            $customerUserEmail = $customerUser->getEmail();

            $this->customerUserFacade->delete($id);
            $this->watchdogFacade->deleteByEmail($customerUserEmail);

            $this->addSuccessFlashTwig(
                t('Customer <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (CustomerUserNotFoundException $ex) {
            $this->addErrorFlash(t('Selected customer doesn\'t exist.'));
        }

        if ($this->customerFacade->isB2bFeaturesEnabledByCustomer($customer)) {
            $billingAddress = $customer->getBillingAddress();

            return $this->redirectToRoute('admin_billing_address_edit', ['id' => $billingAddress->getId()]);
        }

        return $this->redirectToRoute('admin_customer_list');
    }

    #[Route(path: '/customer/delete-all/{id}', name: 'admin_customer_delete_all', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAllAction(int $id): Response
    {
        $customer = $this->customerFacade->getById($id);

        try {
            $fullName = $customer->getBillingAddress()->getCompanyName();

            $this->customerFacade->deleteAll($customer);

            $this->addSuccessFlashTwig(
                t('Customer <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (CustomerUserNotFoundException $ex) {
            $this->addErrorFlash(t('Selected customer doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_customer_list');
    }

    #[Route(path: '/customer/send-reset-password/{id}', name: 'admin_customer_send_reset_password', requirements: ['id' => '\d+'])]
    #[CanEdit]
    #[CsrfProtection]
    public function sendResetPasswordAction(int $id): Response
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById($id);

        $this->customerUserPasswordFacade->resetPassword($customerUser->getEmail(), $customerUser->getDomainId());

        $this->addSuccessFlashTwig(
            t('Reset password request was sent to <strong>{{ email }}</strong>'),
            [
                'email' => $customerUser->getEmail(),
            ],
        );

        $customer = $customerUser->getCustomer();

        if ($this->customerFacade->isB2bFeaturesEnabledByCustomer($customer)) {
            $billingAddress = $customer->getBillingAddress();

            return $this->redirectToRoute('admin_billing_address_edit', ['id' => $billingAddress->getId()]);
        }

        return $this->redirectToRoute('admin_customer_list');
    }

    protected function manipulateRow(array $row): array
    {
        $domain = $this->domain->getDomainConfigById($row['domainId']);

        $row['isB2bCompany'] = $domain->isB2b() && $row['isCompanyCustomer'];

        return $row;
    }

    protected function resolveBackUrl(Customer $customer): string
    {
        if ($this->customerFacade->isB2bFeaturesEnabledByCustomer($customer)) {
            $billingAddress = $customer->getBillingAddress();

            return $this->generateUrl('admin_billing_address_edit', ['id' => $billingAddress->getId()]);
        }

        $customerUsers = $this->customerFacade->getCustomerUsers($customer);
        $firstCustomerUser = reset($customerUsers);

        return $this->generateUrl('admin_customer_edit', ['id' => $firstCustomerUser->getId()]);
    }

    protected function resolveBackUrlText(Customer $customer): string
    {
        if ($this->customerFacade->isB2bFeaturesEnabledByCustomer($customer)) {
            $billingAddress = $customer->getBillingAddress();

            return t('Back to customer {{ name }}', ['{{ name }}' => $billingAddress->getCompanyName()]);
        }

        $customerUsers = $this->customerFacade->getCustomerUsers($customer);
        $firstCustomerUser = reset($customerUsers);

        return t('Back to customer {{ name }}', ['{{ name }}' => $firstCustomerUser->getFullName()]);
    }
}
