<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\MoneyConvertingDataSourceDecorator;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Controller\Admin\CustomerController as BaseCustomerController;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @property \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @property \App\Model\Security\LoginAsUserFacade $loginAsUserFacade
 * @property \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
 * @method __construct(\App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserListAdminFacade $customerUserListAdminFacade, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \App\Model\Order\OrderFacade $orderFacade, \App\Model\Security\LoginAsUserFacade $loginAsUserFacade, \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory, \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 * @method string getSsoLoginAsCustomerUserUrl(\App\Model\Customer\User\CustomerUser $customerUser)
 * @property \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 */
class CustomerController extends BaseCustomerController
{
    /**
     * @Route("/customer/list/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function listAction(Request $request)
    {
        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getUser();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->customerUserListAdminFacade->getCustomerUserListQueryBuilderByQuickSearchData(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $quickSearchForm->getData(),
        );
        $queryBuilder->addSelect('BOOL_AND(ba.activated) as isActivated');

        $innerDataSource = new QueryBuilderDataSource($queryBuilder, 'u.id');
        $dataSource = new MoneyConvertingDataSourceDecorator($innerDataSource, ['ordersSumPrice']);

        $grid = $this->gridFactory->create('customerList', $dataSource);
        $grid->enablePaging();
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'name', t('Full name'), true);
        $grid->addColumn('city', 'city', t('City'), true);
        $grid->addColumn('telephone', 'u.telephone', t('Telephone'), true);
        $grid->addColumn('email', 'u.email', t('Email'), true);
        $grid->addColumn('isActivated', 'isActivated', t('Active'), true);
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
     * @Route("/customer/login-as-user/{customerUserId}/")
     * @param int $customerUserId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAsCustomerUserAction(int $customerUserId): Response
    {
        try {
            return $this->render('Admin/Content/Login/loginAsCustomerUser.html.twig', [
                'tokens' => $this->loginAsUserFacade->loginAsCustomerUserAndGetAccessAndRefreshToken($customerUserId),
                'url' => $this->generateUrl('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        } catch (CustomerUserNotFoundException $e) {
            $this->addErrorFlash(t('Customer not found.'));

            return $this->redirectToRoute('admin_customer_list');
        } catch (LoginAsRememberedUserException $e) {
            throw $this->createAccessDeniedException('Access denied', $e);
        }
    }
}
