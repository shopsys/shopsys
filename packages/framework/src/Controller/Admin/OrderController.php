<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\Grid\EntityLogGridFactory;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderFacade $advancedSearchOrderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade $orderItemFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\Grid\EntityLogGridFactory $entityLogGridFactory
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly AdvancedSearchOrderFacade $advancedSearchOrderFacade,
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly OrderItemFacade $orderItemFacade,
        protected readonly Domain $domain,
        protected readonly OrderDataFactoryInterface $orderDataFactory,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly EntityLogGridFactory $entityLogGridFactory,
    ) {
    }

    /**
     * @Route("/order/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $order = $this->orderFacade->getById($id);

        $orderData = $this->orderDataFactory->createFromOrder($order);

        $form = $this->createForm(OrderFormType::class, $orderData, ['order' => $order]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $order = $this->orderFacade->edit($id, $orderData);

                $this->addSuccessFlashTwig(
                    t('Order Nr. <strong><a href="{{ url }}">{{ number }}</a></strong> modified'),
                    [
                        'number' => $order->getNumber(),
                        'url' => $this->generateUrl('admin_order_edit', ['id' => $order->getId()]),
                    ],
                );

                return $this->redirectToRoute('admin_order_list');
            } catch (CustomerUserNotFoundException) {
                $this->addErrorFlash(
                    t('Entered customer not found, please check entered data.'),
                );
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing order - Nr. %number%', ['%number%' => $order->getNumber()]),
        );

        $entityLogGrid = $this->entityLogGridFactory->createByEntityNameAndEntityId(
            EntityLogFacade::getEntityNameByEntity($order),
            $order->getId(),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Order/edit.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'entityLogGridView' => $entityLogGrid->createView(),
        ]);
    }

    /**
     * @Route("/order/add-product/{orderId}", requirements={"orderId" = "\d+"}, condition="request.isXmlHttpRequest()")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $orderId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addProductAction(Request $request, int $orderId): Response
    {
        $productId = (int)$request->request->get('productId');
        $orderItem = $this->orderItemFacade->addProductToOrder($orderId, $productId);

        $order = $this->orderFacade->getById($orderId);

        $orderData = $this->orderDataFactory->createFromOrder($order);

        $form = $this->createForm(OrderFormType::class, $orderData, ['order' => $order]);

        $orderItemTotalPricesById = $this->orderItemPriceCalculation->calculateTotalPricesIndexedById(
            $order->getItems(),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Order/addProduct.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'orderItem' => $orderItem,
            'orderItemTotalPricesById' => $orderItemTotalPricesById,
        ]);
    }

    /**
     * @Route("/order/list/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request): Response
    {
        $domainFilterNamespace = 'orders';

        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator */
        $administrator = $this->getUser();
        $advancedSearchForm = $this->advancedSearchOrderFacade->createAdvancedSearchOrderForm($request);
        $advancedSearchData = $advancedSearchForm->getData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $isAdvancedSearchFormSubmitted = $this->advancedSearchOrderFacade->isAdvancedSearchOrderFormSubmitted(
            $request,
        );

        if ($isAdvancedSearchFormSubmitted) {
            $queryBuilder = $this->advancedSearchOrderFacade->getQueryBuilderByAdvancedSearchOrderData(
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
        }

        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'o.id',
            function ($row) {
                return $this->addOrderEntityToDataSource($row);
            },
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
        $grid->addEditActionColumn('admin_order_edit', ['id' => 'id']);
        $grid->addDeleteActionColumn('admin_order_delete', ['id' => 'id'])
            ->setConfirmMessage(t('Do you really want to remove the order?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Order/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('@ShopsysFramework/Admin/Content/Order/list.html.twig', [
            'gridView' => $grid->createView(),
            'domainFilterNamespace' => $domainFilterNamespace,
            'quickSearchForm' => $quickSearchForm->createView(),
            'advancedSearchForm' => $advancedSearchForm->createView(),
            'isAdvancedSearchFormSubmitted' => $this->advancedSearchOrderFacade->isAdvancedSearchOrderFormSubmitted(
                $request,
            ),
        ]);
    }

    /**
     * @param array $row
     * @return array
     */
    protected function addOrderEntityToDataSource(array $row): array
    {
        $row['order'] = $this->orderFacade->getById($row['id']);

        return $row;
    }

    /**
     * @Route("/order/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * @Route("/order/get-advanced-search-rule-form/", methods={"post"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getRuleFormAction(Request $request): Response
    {
        $ruleForm = $this->advancedSearchOrderFacade->createRuleForm(
            $request->get('filterName'),
            $request->get('newIndex'),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Order/AdvancedSearch/ruleForm.html.twig', [
            'rulesForm' => $ruleForm->createView(),
        ]);
    }

    /**
     * @Route("/order/preview/{id}", requirements={"id" = "\d+"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function previewAction(int $id): Response
    {
        $order = $this->orderFacade->getById($id);

        return $this->render('@ShopsysFramework/Admin/Content/Order/preview.html.twig', [
            'order' => $order,
        ]);
    }
}
