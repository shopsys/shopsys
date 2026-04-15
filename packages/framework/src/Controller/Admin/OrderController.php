<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Admin\OrderDetail\OrderDetailTabCollector;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogRepository;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderAddressesFormType;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderBillingFormType;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderDeliveryFormType;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderNoteFormType;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderPaymentsFormType;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderPersonalFormType;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderStatusFormType;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderTrackingFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Form\OrderItemsType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\OrderAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_ORDER)]
class OrderController extends AdminBaseController
{
    protected const string ORDERS_LIST_FOR_GRID_CACHE_KEY = 'ORDERS_LIST_FOR_GRID_CACHE_KEY';

    /**
     * Number of audit log entries rendered up-front in the order detail history tab.
     * The remaining entries are fetched on demand via {@see self::historyAction()} so the
     * initial page render does not have to materialize the full audit log into memory.
     */
    protected const int HISTORY_PREVIEW_LIMIT = 20;

    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderAdvancedSearchFacade $orderAdvancedSearchFacade,
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly OrderItemFacade $orderItemFacade,
        protected readonly Domain $domain,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly EntityLogFacade $entityLogFacade,
        protected readonly PricingSetting $pricingSetting,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
        protected readonly OrderDetailTabCollector $orderDetailTabCollector,
        protected readonly EntityLogRepository $entityLogRepository,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
        protected readonly OrderStatusFacade $orderStatusFacade,
    ) {
    }

    #[Route(path: '/order/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanView]
    public function editAction(Request $request, int $id): Response
    {
        $order = $this->orderFacade->getById($id);

        return $this->renderEditPage($order);
    }

    #[Route(path: '/order/edit/{id}/save/{section}', requirements: ['id' => '\d+'], methods: ['POST'], condition: 'request.isXmlHttpRequest()')]
    #[CanEdit]
    public function saveSectionAction(Request $request, int $id, string $section): JsonResponse
    {
        $order = $this->orderFacade->getById($id);
        $orderData = $this->orderDataFactory->createFromOrder($order);

        $formTypeClass = $this->getSectionFormTypeClass($section);
        $formOptions = [
            'inherit_data' => false,
            'data_class' => OrderData::class,
        ];

        if (in_array($formTypeClass, [OrderStatusFormType::class, OrderAddressesFormType::class, OrderBillingFormType::class, OrderDeliveryFormType::class, OrderPaymentsFormType::class], true)) {
            $formOptions['order'] = $order;
        }

        $form = $this->createForm($formTypeClass, $orderData, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->orderFacade->edit($id, $orderData);
            $order = $this->orderFacade->getById($id);
            $orderData = $this->orderDataFactory->createFromOrder($order);

            $viewHtml = $this->renderView(
                $this->getSectionViewTemplate($section),
                [
                    'order' => $order,
                    'orderData' => $orderData,
                    'withdrawalRequest' => $this->withdrawalRequestFacade->findByOrder($order),
                ],
            );

            $summaryHtml = $this->renderView(
                '@ShopsysAdministration/content/order/partials/summary_bar.html.twig',
                [
                    'order' => $order,
                    'orderData' => $orderData,
                    'canEdit' => true,
                    'orderStatuses' => $this->orderStatusFacade->getAll(),
                ],
            );

            return new JsonResponse([
                'success' => true,
                'viewHtml' => $viewHtml,
                'summaryHtml' => $summaryHtml,
            ]);
        }

        $formHtml = $this->renderView(
            $this->getSectionFormTemplate($section),
            ['form' => $form->createView(), 'order' => $order],
        );

        return new JsonResponse([
            'success' => false,
            'formHtml' => $formHtml,
        ]);
    }

    #[Route(path: '/order/edit/{id}/change-status', requirements: ['id' => '\d+'], methods: ['POST'], condition: 'request.isXmlHttpRequest()')]
    #[CanEdit]
    #[CsrfProtection]
    public function changeStatusAction(Request $request, int $id): JsonResponse
    {
        $order = $this->orderFacade->getById($id);
        $statusId = (int)$request->request->get('statusId');
        $status = $this->orderStatusFacade->getById($statusId);

        // Switching to "withdrawn" must always go through the withdrawal modal so that
        // OrderStatusFormType validates the required withdrawal request data. The dropdown
        // would otherwise persist the new status and trigger status side effects (e.g. emails)
        // without ever asking the user for the withdrawal payload.
        if ($status->getType() === OrderStatusTypeEnum::TYPE_WITHDRAWN) {
            return new JsonResponse([
                'success' => false,
                'requiresWithdrawalModal' => true,
            ]);
        }

        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->status = $status;
        $this->orderFacade->edit($id, $orderData);

        return new JsonResponse(['success' => true]);
    }

    #[Route(path: '/order/edit/{id}/save/items', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[CanEdit]
    public function saveItemsAction(Request $request, int $id): Response
    {
        $order = $this->orderFacade->getById($id);
        $orderData = $this->orderDataFactory->createFromOrder($order);

        $form = $this->createFormBuilder($orderData, [
            'data_class' => OrderData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ])
            ->add('orderItems', OrderItemsType::class, ['order' => $order])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->orderFacade->edit($id, $orderData);

            $this->addSuccessFlashTwig(
                t('Order Nr. <strong><a href="{{ url }}">{{ number }}</a></strong> modified'),
                [
                    'number' => $order->getNumber(),
                    'url' => $this->generateUrl('admin_order_edit', ['id' => $order->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_order_edit', ['id' => $id, '_fragment' => 'tab-items']);
        }

        $this->addErrorFlash(t('Please check the correctness of all data filled.'));

        return $this->renderEditPage($order, $form, null, 'items');
    }

    /**
     * Standalone full-page submit endpoint for the payments tab.
     *
     * Other order detail sections (personal, addresses, note, tracking) are saved via AJAX modals
     * through saveSectionAction(). Payments cannot reuse that flow because the refund button
     * triggers an external GoPay API call inside OrderFacade::edit() and we want the page to
     * reload with fresh transaction state afterwards.
     *
     * The GET branch redirects to the edit page so that a browser refresh on this URL
     * (e.g. after a validation error) does not produce a 405 Method Not Allowed.
     */
    #[Route(path: '/order/edit/{id}/save/payments', requirements: ['id' => '\d+'], methods: ['POST', 'GET'])]
    #[CanEdit]
    public function savePaymentsAction(Request $request, int $id): Response
    {
        if ($request->isMethod('GET')) {
            return $this->redirectToRoute('admin_order_edit', ['id' => $id, '_fragment' => 'tab-payments']);
        }

        $order = $this->orderFacade->getById($id);
        $orderData = $this->orderDataFactory->createFromOrder($order);

        $form = $this->createForm(OrderPaymentsFormType::class, $orderData, [
            'inherit_data' => false,
            'data_class' => OrderData::class,
            'order' => $order,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->orderFacade->edit($id, $orderData);

            $this->addSuccessFlashTwig(
                t('Order Nr. <strong><a href="{{ url }}">{{ number }}</a></strong> modified'),
                [
                    'number' => $order->getNumber(),
                    'url' => $this->generateUrl('admin_order_edit', ['id' => $order->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_order_edit', ['id' => $id, '_fragment' => 'tab-payments']);
        }

        $this->addErrorFlash(t('Please check the correctness of all data filled.'));

        return $this->renderEditPage($order, null, $form, 'payments');
    }

    #[Route(path: '/order/add-product/{orderId}', requirements: ['orderId' => '\d+'], condition: 'request.isXmlHttpRequest()')]
    #[CanEdit]
    public function addProductAction(Request $request, int $orderId): Response
    {
        $productId = (int)$request->request->get('productId');
        $orderItem = $this->orderItemFacade->addProductToOrder($orderId, $productId);

        $order = $this->orderFacade->getById($orderId);
        $orderData = $this->orderDataFactory->createFromOrder($order);

        $form = $this->createFormBuilder($orderData, ['attr' => ['novalidate' => 'novalidate']])
            ->add('orderItems', OrderItemsType::class, ['order' => $order])
            ->getForm();

        $orderItemTotalPricesById = $this->orderItemPriceCalculation->calculateTotalPricesIndexedById(
            $order->getItems(),
        );

        return $this->render('@ShopsysAdministration/content/order/addProduct.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'orderItem' => $orderItem,
            'orderItemTotalPricesById' => $orderItemTotalPricesById,
            'inputPriceType' => $this->pricingSetting->getInputPriceType(),
        ]);
    }

    #[Route(path: '/order/list/')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $domainFilterNamespace = 'orders';

        $advancedSearchForm = $this->orderAdvancedSearchFacade->createAdvancedSearchForm($request);
        $advancedSearchData = $advancedSearchForm->getData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $isAdvancedSearchFormSubmitted = $this->orderAdvancedSearchFacade->isAdvancedSearchFormSubmitted(
            $request,
        );

        if ($isAdvancedSearchFormSubmitted) {
            $queryBuilder = $this->orderAdvancedSearchFacade->getQueryBuilderByAdvancedSearchOrderData(
                $advancedSearchData,
            );
        } else {
            $queryBuilder = $this->orderFacade->getOrderListQueryBuilderByQuickSearchData($quickSearchForm->getData());
        }

        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId($domainFilterNamespace);

        if ($selectedDomainId !== null) {
            $queryBuilder
                ->andWhere('o.domainId = :selectedDomainId')
                ->setParameter('selectedDomainId', $selectedDomainId);
        } else {
            $queryBuilder
                ->andWhere('o.domainId IN (:domainIds)')
                ->setParameter('domainIds', $this->domain->getAdminEnabledDomainIds());
        }

        $grid = $this->getOrdersGrid($queryBuilder);

        return $this->render('@ShopsysAdministration/content/order/list.html.twig', [
            'gridView' => $grid->createView(),
            'domainFilterNamespace' => $domainFilterNamespace,
            'quickSearchForm' => $quickSearchForm->createView(),
            'advancedSearchForm' => $advancedSearchForm->createView(),
            'isAdvancedSearchFormSubmitted' => $this->orderAdvancedSearchFacade->isAdvancedSearchFormSubmitted(
                $request,
            ),
        ]);
    }

    /**
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
     */
    protected function getOrdersGrid(QueryBuilder $queryBuilder): Grid
    {
        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
            $queryBuilder,
            'o.id',
            function ($row, $rows) {
                return $this->addOrderEntityToDataSource($row, array_column($rows, 'id'));
            },
            null,
        );

        $grid = $this->gridFactory->create('orderList', $dataSource, AdminRoleConstant::ROLE_ORDER);
        $grid->enablePaging();
        $grid->setDefaultOrder('created_at', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('number', 'o.number', t('Order Nr.'), true);
        $grid->addColumn('created_at', 'o.createdAt', t('Created'), true);
        $grid->addColumn('customer_name', 'customerName', t('Customer'), true);
        $grid->addColumn('status_name', 'statusName', t('Status'), true);
        $grid->addColumn('total_price', 'o.totalPriceWithVat', t('Total price'), false)
            ->setClassAttribute('text-end text-nowrap');

        if ($this->domain->isMultidomain()) {
            $grid->addColumn('domain_id', 'o.domainId', t('Domain'), true)->setClassAttribute('w-1 d-none d-md-table-cell text-center');
        }


        $grid->addEditActionColumn('admin_order_edit', ['id' => 'id']);
        $grid->addDeleteActionColumn('admin_order_delete', ['id' => 'id'])
            ->setConfirmMessage(t('Do you really want to remove the order?'));

        $grid->setActionColumnClassAttribute('text-center');
        $grid->setTheme('@ShopsysAdministration/content/order/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $grid;
    }

    /**
     * @param int[] $ids
     */
    protected function addOrderEntityToDataSource(array $row, array $ids): array
    {
        $ordersIndexedById = $this->inMemoryCache->getOrSaveValue(
            self::ORDERS_LIST_FOR_GRID_CACHE_KEY,
            fn () => $this->orderFacade->findByIds($ids),
            self::ORDERS_LIST_FOR_GRID_CACHE_KEY,
            ...$ids,
        );

        $row['order'] = $ordersIndexedById[$row['id']];

        return $row;
    }

    #[Route(path: '/order/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $orderNumber = $this->orderFacade->getById($id)->getNumber();

            $this->orderFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Order Nr. <strong>{{ number }}</strong> deleted'),
                [
                    'number' => $orderNumber,
                ],
            );
        } catch (OrderNotFoundException) {
            $this->addErrorFlash(t('Selected order doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_order_list');
    }

    #[Route(path: '/order/preview/{id}', requirements: ['id' => '\d+'])]
    #[CanView]
    public function previewAction(int $id): Response
    {
        $order = $this->orderFacade->getById($id);

        return $this->render('@ShopsysAdministration/content/order/preview.html.twig', [
            'order' => $order,
        ]);
    }

    protected function getSectionFormTypeClass(string $section): string
    {
        return match ($section) {
            'status' => OrderStatusFormType::class,
            'withdrawal' => OrderStatusFormType::class,
            'personal' => OrderPersonalFormType::class,
            'addresses' => OrderAddressesFormType::class,
            'billing' => OrderBillingFormType::class,
            'delivery' => OrderDeliveryFormType::class,
            'note' => OrderNoteFormType::class,
            'tracking' => OrderTrackingFormType::class,
            'payments' => OrderPaymentsFormType::class,
            default => throw $this->createNotFoundException(),
        };
    }

    protected function getSectionViewTemplate(string $section): string
    {
        return match ($section) {
            'status' => '@ShopsysAdministration/content/order/partials/status_view.html.twig',
            'withdrawal' => '@ShopsysAdministration/content/order/partials/withdrawal_view.html.twig',
            'personal' => '@ShopsysAdministration/content/order/partials/personal_view.html.twig',
            'addresses' => '@ShopsysAdministration/content/order/partials/addresses_view.html.twig',
            'billing' => '@ShopsysAdministration/content/order/partials/addresses_view.html.twig',
            'delivery' => '@ShopsysAdministration/content/order/partials/addresses_view.html.twig',
            'tracking' => '@ShopsysAdministration/content/order/partials/status_view.html.twig',
            'note' => '@ShopsysAdministration/content/order/partials/note_view.html.twig',
            'payments' => '@ShopsysAdministration/content/order/partials/payments_view.html.twig',
            default => throw $this->createNotFoundException(),
        };
    }

    protected function getSectionFormTemplate(string $section): string
    {
        return match ($section) {
            'status' => '@ShopsysAdministration/content/order/partials/status_form.html.twig',
            'withdrawal' => '@ShopsysAdministration/content/order/partials/withdrawal_form.html.twig',
            'tracking' => '@ShopsysAdministration/content/order/partials/tracking_form.html.twig',
            'personal' => '@ShopsysAdministration/content/order/partials/personal_form.html.twig',
            'addresses' => '@ShopsysAdministration/content/order/partials/addresses_form.html.twig',
            'billing' => '@ShopsysAdministration/content/order/partials/billing_form.html.twig',
            'delivery' => '@ShopsysAdministration/content/order/partials/delivery_form.html.twig',
            'note' => '@ShopsysAdministration/content/order/partials/note_form.html.twig',
            'payments' => '@ShopsysAdministration/content/order/partials/payments_form.html.twig',
            default => throw $this->createNotFoundException(),
        };
    }

    /**
     * @param \Symfony\Component\Form\FormInterface|null $invalidItemsForm submitted-but-invalid items form to keep validation errors visible
     * @param \Symfony\Component\Form\FormInterface|null $invalidPaymentsForm same as $invalidItemsForm but for the payments tab
     * @param string|null $activeTab tab identifier to pre-select after re-render (e.g. after a validation error). Defaults to the first tab.
     */
    protected function renderEditPage(
        Order $order,
        ?FormInterface $invalidItemsForm = null,
        ?FormInterface $invalidPaymentsForm = null,
        ?string $activeTab = null,
    ): Response {
        $orderData = $this->orderDataFactory->createFromOrder($order);
        $canEdit = $this->accessChecker->canEdit(AdminRoleConstant::ROLE_ORDER);
        $standaloneOptions = ['inherit_data' => false, 'data_class' => OrderData::class];

        $statusForm = $this->createForm(OrderStatusFormType::class, $orderData, $standaloneOptions + ['order' => $order]);
        $personalForm = $this->createForm(OrderPersonalFormType::class, $orderData, $standaloneOptions);
        $billingForm = $this->createForm(OrderBillingFormType::class, $orderData, $standaloneOptions + ['order' => $order]);
        $deliveryForm = $this->createForm(OrderDeliveryFormType::class, $orderData, $standaloneOptions + ['order' => $order]);
        $noteForm = $this->createForm(OrderNoteFormType::class, $orderData, $standaloneOptions);
        $trackingForm = $this->createForm(OrderTrackingFormType::class, $orderData, $standaloneOptions);
        $withdrawalForm = $this->createForm(OrderStatusFormType::class, $orderData, $standaloneOptions + ['order' => $order]);

        $itemsForm = $invalidItemsForm ?? $this->createFormBuilder($orderData, ['attr' => ['novalidate' => 'novalidate']])
            ->add('orderItems', OrderItemsType::class, ['order' => $order])
            ->getForm();

        $paymentsForm = null;

        if ($order->getPaymentTransactionsCount() > 0) {
            $paymentsForm = $invalidPaymentsForm ?? $this->createForm(OrderPaymentsFormType::class, $orderData, $standaloneOptions + ['order' => $order]);
        }

        $tabs = $this->orderDetailTabCollector->getTabs($order);

        $entityName = $this->entityLogFacade->getEntityNameByEntity($order);
        $entityLogEntries = $this->entityLogRepository
            ->getQueryBuilderByEntityNameAndEntityId($entityName, $order->getId())
            ->setMaxResults(self::HISTORY_PREVIEW_LIMIT)
            ->getQuery()
            ->getResult();
        $entityLogTotalCount = $this->entityLogRepository
            ->getCountByEntityNameAndEntityId($entityName, $order->getId());

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing order - Nr. %number%', ['%number%' => $order->getNumber()]),
        );

        return $this->render('@ShopsysAdministration/content/order/edit.html.twig', [
            'order' => $order,
            'orderData' => $orderData,
            'tabs' => $tabs,
            'activeTab' => $activeTab,
            'statusForm' => $statusForm->createView(),
            'personalForm' => $personalForm->createView(),
            'billingForm' => $billingForm->createView(),
            'deliveryForm' => $deliveryForm->createView(),
            'noteForm' => $noteForm->createView(),
            'itemsForm' => $itemsForm->createView(),
            'paymentsForm' => $paymentsForm?->createView(),
            'entityLogEntries' => $entityLogEntries,
            'entityLogTotalCount' => $entityLogTotalCount,
            'trackingForm' => $trackingForm->createView(),
            'withdrawalForm' => $withdrawalForm->createView(),
            'withdrawalRequest' => $this->withdrawalRequestFacade->findByOrder($order),
            'canEdit' => $canEdit,
            'orderStatuses' => $this->orderStatusFacade->getAll(),
        ]);
    }

    #[Route(path: '/order/edit/{id}/history', requirements: ['id' => '\d+'], methods: ['GET'], condition: 'request.isXmlHttpRequest()')]
    #[CanView]
    public function historyAction(int $id): JsonResponse
    {
        $order = $this->orderFacade->getById($id);

        $entityLogEntries = $this->entityLogRepository
            ->getQueryBuilderByEntityNameAndEntityId(
                $this->entityLogFacade->getEntityNameByEntity($order),
                $order->getId(),
            )
            ->getQuery()
            ->getResult();

        $html = $this->renderView(
            '@ShopsysAdministration/content/order/partials/history_timeline.html.twig',
            [
                'order' => $order,
                'entityLogEntries' => $entityLogEntries,
            ],
        );

        return new JsonResponse(['html' => $html]);
    }
}
