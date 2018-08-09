<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider
     */
    private $breadcrumbOverrider;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade
     */
    private $administratorGridFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderFacade
     */
    private $advancedSearchOrderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private $gridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation
     */
    private $orderItemPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     */
    private $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade
     */
    private $orderItemFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    private $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     */
    private $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface
     */
    private $orderDataFactory;

    public function __construct(
        OrderFacade $orderFacade,
        AdvancedSearchOrderFacade $advancedSearchOrderFacade,
        OrderItemPriceCalculation $orderItemPriceCalculation,
        AdministratorGridFacade $administratorGridFacade,
        GridFactory $gridFactory,
        BreadcrumbOverrider $breadcrumbOverrider,
        OrderItemFacade $orderItemFacade,
        TransportFacade $transportFacade,
        PaymentFacade $paymentFacade,
        Domain $domain,
        OrderDataFactoryInterface $orderDataFactory
    ) {
        $this->orderFacade = $orderFacade;
        $this->advancedSearchOrderFacade = $advancedSearchOrderFacade;
        $this->orderItemPriceCalculation = $orderItemPriceCalculation;
        $this->administratorGridFacade = $administratorGridFacade;
        $this->gridFactory = $gridFactory;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
        $this->orderItemFacade = $orderItemFacade;
        $this->transportFacade = $transportFacade;
        $this->paymentFacade = $paymentFacade;
        $this->domain = $domain;
        $this->orderDataFactory = $orderDataFactory;
    }

    /**
     * @Route("/order/edit/{id}", requirements={"id" = "\d+"})
     * @param int $id
     */
    public function editAction(Request $request, $id)
    {
        $order = $this->orderFacade->getById($id);

        $orderData = $this->orderDataFactory->createFromOrder($order);

        $form = $this->createForm(OrderFormType::class, $orderData, ['order' => $order]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $order = $this->orderFacade->edit($id, $orderData);

                $this->getFlashMessageSender()->addSuccessFlashTwig(
                    t('Order Nr. <strong><a href="{{ url }}">{{ number }}</a></strong> modified'),
                    [
                        'number' => $order->getNumber(),
                        'url' => $this->generateUrl('admin_order_edit', ['id' => $order->getId()]),
                    ]
                );
                return $this->redirectToRoute('admin_order_list');
            } catch (\Shopsys\FrameworkBundle\Model\Customer\Exception\UserNotFoundException $e) {
                $this->getFlashMessageSender()->addErrorFlash(
                    t('Entered customer not found, please check entered data.')
                );
            } catch (\Shopsys\FrameworkBundle\Model\Mail\Exception\MailException $e) {
                $this->getFlashMessageSender()->addErrorFlash(t('Unable to send updating e-mail'));
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing order - Nr. %number%', ['%number%' => $order->getNumber()]));

        $orderItemTotalPricesById = $this->orderItemPriceCalculation->calculateTotalPricesIndexedById($order->getItems());

        return $this->render('@ShopsysFramework/Admin/Content/Order/edit.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'orderItemTotalPricesById' => $orderItemTotalPricesById,
            'transportPricesWithVatByTransportId' => $this->transportFacade->getTransportPricesWithVatIndexedByTransportId(
                $order->getCurrency()
            ),
            'transportVatPercentsByTransportId' => $this->transportFacade->getTransportVatPercentsIndexedByTransportId(),
            'paymentPricesWithVatByPaymentId' => $this->paymentFacade->getPaymentPricesWithVatIndexedByPaymentId(
                $order->getCurrency()
            ),
            'paymentVatPercentsByPaymentId' => $this->paymentFacade->getPaymentVatPercentsIndexedByPaymentId(),
        ]);
    }

    /**
     * @Route("/order/add-product/{orderId}", requirements={"orderId" = "\d+"}, condition="request.isXmlHttpRequest()")
     * @param int $orderId
     */
    public function addProductAction(Request $request, $orderId)
    {
        $productId = $request->get('productId');
        $orderItem = $this->orderItemFacade->createOrderProductInOrder($orderId, $productId);

        $order = $this->orderFacade->getById($orderId);

        $orderData = $this->orderDataFactory->createFromOrder($order);

        $form = $this->createForm(OrderFormType::class, $orderData, ['order' => $order]);

        $orderItemTotalPricesById = $this->orderItemPriceCalculation->calculateTotalPricesIndexedById($order->getItems());

        return $this->render('@ShopsysFramework/Admin/Content/Order/addProduct.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'orderItem' => $orderItem,
            'orderItemTotalPricesById' => $orderItemTotalPricesById,
        ]);
    }

    /**
     * @Route("/order/list/")
     */
    public function listAction(Request $request)
    {
        $administrator = $this->getUser();
        /* @var $administrator \Shopsys\FrameworkBundle\Model\Administrator\Administrator */

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
        $grid->addEditActionColumn('admin_order_edit', ['id' => 'id']);
        $grid->addDeleteActionColumn('admin_order_delete', ['id' => 'id'])
            ->setConfirmMessage(t('Do you really want to remove the order?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Order/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('@ShopsysFramework/Admin/Content/Order/list.html.twig', [
            'gridView' => $grid->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
            'advancedSearchForm' => $advancedSearchForm->createView(),
            'isAdvancedSearchFormSubmitted' => $this->advancedSearchOrderFacade->isAdvancedSearchOrderFormSubmitted($request),
        ]);
    }

    /**
     * @return array
     */
    private function addOrderEntityToDataSource(array $row)
    {
        $row['order'] = $this->orderFacade->getById($row['id']);

        return $row;
    }

    /**
     * @Route("/order/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     */
    public function deleteAction($id)
    {
        try {
            $orderNumber = $this->orderFacade->getById($id)->getNumber();

            $this->orderFacade->deleteById($id);

            $this->getFlashMessageSender()->addSuccessFlashTwig(
                t('Order Nr. <strong>{{ number }}</strong> deleted'),
                [
                    'number' => $orderNumber,
                ]
            );
        } catch (\Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException $ex) {
            $this->getFlashMessageSender()->addErrorFlash(t('Selected order doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_order_list');
    }

    /**
     * @Route("/order/get-advanced-search-rule-form/", methods={"post"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getRuleFormAction(Request $request)
    {
        $ruleForm = $this->advancedSearchOrderFacade->createRuleForm($request->get('filterName'), $request->get('newIndex'));

        return $this->render('@ShopsysFramework/Admin/Content/Order/AdvancedSearch/ruleForm.html.twig', [
            'rulesForm' => $ruleForm->createView(),
        ]);
    }

    /**
     * @Route("/order/preview/{id}", requirements={"id" = "\d+"})
     * @param int $id
     */
    public function previewAction($id)
    {
        $order = $this->orderFacade->getById($id);

        return $this->render('@ShopsysFramework/Admin/Content/Order/preview.html.twig', [
            'order' => $order,
        ]);
    }
}
