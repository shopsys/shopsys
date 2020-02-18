<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\MoneyConvertingDataSourceDecorator;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserUpdateFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserListAdminFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerController extends AdminBaseController
{
    protected const LOGIN_AS_TOKEN_ID_PREFIX = 'loginAs';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface
     */
    protected $customerUserDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserListAdminFacade
     */
    protected $customerUserListAdminFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    protected $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider
     */
    protected $breadcrumbOverrider;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade
     */
    protected $administratorGridFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    protected $gridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    protected $adminDomainTabsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     */
    protected $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade
     */
    protected $loginAsUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    protected $domainRouterFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface
     */
    protected $customerUserUpdateDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface $customerUserDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserListAdminFacade $customerUserListAdminFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade $loginAsUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     */
    public function __construct(
        CustomerUserDataFactoryInterface $customerUserDataFactory,
        CustomerUserListAdminFacade $customerUserListAdminFacade,
        CustomerUserFacade $customerUserFacade,
        BreadcrumbOverrider $breadcrumbOverrider,
        AdministratorGridFacade $administratorGridFacade,
        GridFactory $gridFactory,
        AdminDomainTabsFacade $adminDomainTabsFacade,
        OrderFacade $orderFacade,
        LoginAsUserFacade $loginAsUserFacade,
        DomainRouterFactory $domainRouterFactory,
        CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
    ) {
        $this->customerUserDataFactory = $customerUserDataFactory;
        $this->customerUserListAdminFacade = $customerUserListAdminFacade;
        $this->customerUserFacade = $customerUserFacade;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
        $this->administratorGridFacade = $administratorGridFacade;
        $this->gridFactory = $gridFactory;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
        $this->orderFacade = $orderFacade;
        $this->loginAsUserFacade = $loginAsUserFacade;
        $this->domainRouterFactory = $domainRouterFactory;
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
    }

    /**
     * @Route("/customer/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    public function editAction(Request $request, int $id)
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById($id);
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);

        $form = $this->createForm(CustomerUserUpdateFormType::class, $customerUserUpdateData, [
            'customerUser' => $customerUser,
            'domain_id' => $this->adminDomainTabsFacade->getSelectedDomainId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->customerUserFacade->editByAdmin($id, $customerUserUpdateData);

            $this->getFlashMessageSender()->addSuccessFlashTwig(
                t('Customer <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $customerUser->getFullName(),
                    'url' => $this->generateUrl('admin_customer_edit', ['id' => $customerUser->getId()]),
                ]
            );

            return $this->redirectToRoute('admin_customer_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing customer - %name%', ['%name%' => $customerUser->getFullName()]));

        $orders = $this->orderFacade->getCustomerUserOrderList($customerUser);

        return $this->render('@ShopsysFramework/Admin/Content/Customer/edit.html.twig', [
            'form' => $form->createView(),
            'customerUser' => $customerUser,
            'orders' => $orders,
            'ssoLoginAsUserUrl' => $this->getSsoLoginAsCustomerUserUrl($customerUser),
        ]);
    }

    /**
     * @Route("/customer/list/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function listAction(Request $request)
    {
        $administrator = $this->getUser();
        /* @var $administrator \Shopsys\FrameworkBundle\Model\Administrator\Administrator */

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->customerUserListAdminFacade->getCustomerUserListQueryBuilderByQuickSearchData(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $quickSearchForm->getData()
        );

        $innerDataSource = new QueryBuilderDataSource($queryBuilder, 'u.id');
        $dataSource = new MoneyConvertingDataSourceDecorator($innerDataSource, ['ordersSumPrice']);

        $grid = $this->gridFactory->create('customerList', $dataSource);
        $grid->enablePaging();
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'name', t('Full name'), true);
        $grid->addColumn('city', 'city', t('City'), true);
        $grid->addColumn('telephone', 'u.telephone', t('Telephone'), true);
        $grid->addColumn('email', 'u.email', t('E-mail'), true);
        $grid->addColumn('pricingGroup', 'pricingGroup', t('Pricing group'), true);
        $grid->addColumn('orders_count', 'ordersCount', t('Number of orders'), true)->setClassAttribute('text-right');
        $grid->addColumn('orders_sum_price', 'ordersSumPrice', t('Orders value'), true)
            ->setClassAttribute('text-right');
        $grid->addColumn('last_order_at', 'lastOrderAt', t('Last order'), true)
            ->setClassAttribute('text-right');

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_customer_edit', ['id' => 'id']);
        $grid->addDeleteActionColumn('admin_customer_delete', ['id' => 'id'])
            ->setConfirmMessage(t('Do you really want to remove this customer?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Customer/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('@ShopsysFramework/Admin/Content/Customer/list.html.twig', [
            'gridView' => $grid->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    /**
     * @Route("/customer/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function newAction(Request $request)
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
            $customerUserUpdateData = $form->getData();
            $customerUser = $this->customerUserFacade->create($customerUserUpdateData);

            $this->getFlashMessageSender()->addSuccessFlashTwig(
                t('Customer <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $customerUser->getFullName(),
                    'url' => $this->generateUrl('admin_customer_edit', ['id' => $customerUser->getId()]),
                ]
            );

            return $this->redirectToRoute('admin_customer_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Customer/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/customer/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     */
    public function deleteAction($id)
    {
        try {
            $fullName = $this->customerUserFacade->getCustomerUserById($id)->getFullName();

            $this->customerUserFacade->delete($id);

            $this->getFlashMessageSender()->addSuccessFlashTwig(
                t('Customer <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ]
            );
        } catch (\Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException $ex) {
            $this->getFlashMessageSender()->addErrorFlash(t('Selected customer doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_customer_list');
    }

    /**
     * @Route("/customer/login-as-user/{customerUserId}/", requirements={"id" = "\d+"})
     * @param int $customerUserId
     */
    public function loginAsUserAction($customerUserId)
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById($customerUserId);
        $this->loginAsUserFacade->rememberLoginAsUser($customerUser);

        return $this->redirectToRoute('front_customer_login_as_remembered_user');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     *
     * @return string
     */
    protected function getSsoLoginAsCustomerUserUrl(CustomerUser $customerUser)
    {
        $customerDomainRouter = $this->domainRouterFactory->getRouter($customerUser->getDomainId());
        $loginAsUserUrl = $customerDomainRouter->generate(
            'admin_customer_loginasuser',
            [
                'customerUserId' => $customerUser->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $mainAdminDomainRouter = $this->domainRouterFactory->getRouter(Domain::MAIN_ADMIN_DOMAIN_ID);
        $ssoLoginAsUserUrl = $mainAdminDomainRouter->generate(
            'admin_login_sso',
            [
                LoginController::ORIGINAL_DOMAIN_ID_PARAMETER_NAME => $customerUser->getDomainId(),
                LoginController::ORIGINAL_REFERER_PARAMETER_NAME => $loginAsUserUrl,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $ssoLoginAsUserUrl;
    }
}
