<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Security\Roles;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Controller\Admin\OrderController as BaseOrderController;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 * @property \App\Component\Domain\Domain $domain
 */
class OrderController extends BaseOrderController
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        OrderFacade $orderFacade,
        AdvancedSearchOrderFacade $advancedSearchOrderFacade,
        OrderItemPriceCalculation $orderItemPriceCalculation,
        AdministratorGridFacade $administratorGridFacade,
        GridFactory $gridFactory,
        BreadcrumbOverrider $breadcrumbOverrider,
        OrderItemFacade $orderItemFacade,
        Domain $domain,
        OrderDataFactoryInterface $orderDataFactory,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct(
            $orderFacade,
            $advancedSearchOrderFacade,
            $orderItemPriceCalculation,
            $administratorGridFacade,
            $gridFactory,
            $breadcrumbOverrider,
            $orderItemFacade,
            $domain,
            $orderDataFactory
        );
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id): Response
    {
        // prevent editing of order on POST request
        if ($request->getMethod() === Request::METHOD_POST) {
            return $this->redirectToRoute('shopsys_framework_admin_order_edit', ['id' => $id]);
        }

        return parent::editAction($request, $id);
    }

    /**
     * @Route("/order/list/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function listAction(Request $request)
    {
        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getUser();
        $advancedSearchForm = $this->advancedSearchOrderFacade->createAdvancedSearchOrderForm($request);
        $advancedSearchData = $advancedSearchForm->getData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $isAdvancedSearchFormSubmitted = $this->advancedSearchOrderFacade->isAdvancedSearchOrderFormSubmitted($request);
        if ($isAdvancedSearchFormSubmitted) {
            $queryBuilder = $this->advancedSearchOrderFacade->getQueryBuilderByAdvancedSearchOrderData($advancedSearchData);
        } else {
            $queryBuilder = $this->orderFacade->getOrderListQueryBuilderByQuickSearchData($quickSearchForm->getData());
        }

        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'o.id',
            function ($row) {
                return $this->addOrderEntityToDataSource($row);
            }
        );

        $grid = $this->gridFactory->create('orderList', $dataSource);
        $grid->enablePaging();
        $grid->setDefaultOrder('created_at', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('preview', 'o.id', t('Preview'), false);
        $grid->addColumn('number', 'o.number', t('Order Nr.'), true);
        $grid->addColumn('created_at', 'o.createdAt', t('Created'), true);
        $grid->addColumn('customer_name', 'customerName', t('Customer'), true);
        if ($this->domain->isMultidomain()) {
            $grid->addColumn('domain_id', 'o.domainId', t('Domain'), true);
        }
        $grid->addColumn('status_name', 'statusName', t('Status'), true);
        $grid->addColumn('total_price', 'o.totalPriceWithVat', t('Total price'), false)
            ->setClassAttribute('text-right text-no-wrap');

        $grid->setActionColumnClassAttribute('table-col table-col-10');

        if ($this->authorizationChecker->isGranted(Roles::ROLE_CUSTOMER_CARE) === false) {
            $grid->addEditActionColumn('admin_order_edit', ['id' => 'id']);
            $grid->addDeleteActionColumn('admin_order_delete', ['id' => 'id'])
                ->setConfirmMessage(t('Do you really want to remove the order?'));
        }

        $grid->setTheme('@ShopsysFramework/Admin/Content/Order/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('@ShopsysFramework/Admin/Content/Order/list.html.twig', [
            'gridView' => $grid->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
            'advancedSearchForm' => $advancedSearchForm->createView(),
            'isAdvancedSearchFormSubmitted' => $this->advancedSearchOrderFacade->isAdvancedSearchOrderFormSubmitted($request),
        ]);
    }
}
