<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailTabRegistry;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderPaidStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_ORDER)]
class OrderDetailController extends AdminBaseController
{
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly OrderStatusFacade $orderStatusFacade,
        protected readonly OrderDetailTabRegistry $orderDetailTabRegistry,
        protected readonly OrderPaidStatusFacade $orderPaidStatusFacade,
    ) {
    }

    #[Route(path: '/order/edit/{id}', requirements: ['id' => '\d+'], name: 'admin_order_edit')]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $order = $this->orderFacade->getById($id);

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing order - Nr. %number%', ['%number%' => $order->getNumber()]),
        );

        $orderDetailTabs = $this->orderDetailTabRegistry->getTabs($order);

        return $this->render('@ShopsysAdministration/content/order/detail/edit.html.twig', [
            'orderDetailTabs' => $orderDetailTabs,
            'order' => $order,
            'activeTab' => $this->getActiveTabId($orderDetailTabs, $request->query->getString('activeTab'), $order),
        ]);
    }

    #[Route(
        path: '/order/edit/{id}/change-status/{statusId}/withdrawal',
        requirements: ['id' => '\d+', 'statusId' => '\d+'],
        methods: ['GET'],
        name: 'admin_order_edit_withdrawal_status',
    )]
    #[CanEdit]
    public function editWithdrawalStatusAction(Request $request, int $id, int $statusId): Response
    {
        $order = $this->orderFacade->getById($id);
        $status = $this->orderStatusFacade->getById($statusId);

        if ($status->getType() !== OrderStatusTypeEnum::TYPE_WITHDRAWN) {
            throw $this->createNotFoundException();
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing order - Nr. %number%', ['%number%' => $order->getNumber()]),
        );

        $orderDetailTabs = $this->orderDetailTabRegistry->getTabs($order);

        return $this->render('@ShopsysAdministration/content/order/detail/edit_withdrawal_status.html.twig', [
            'orderDetailTabs' => $orderDetailTabs,
            'order' => $order,
            'activeTab' => $this->getActiveTabId($orderDetailTabs, $request->query->getString('activeTab'), $order),
            'withdrawalStatusId' => $statusId,
        ]);
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailTab> $tabs
     */
    protected function getActiveTabId(array $tabs, ?string $requestedActiveTab, Order $order): ?string
    {
        if ($requestedActiveTab !== null && isset($tabs[$requestedActiveTab]) && !$tabs[$requestedActiveTab]->isDisabled($order)) {
            return $requestedActiveTab;
        }

        foreach ($tabs as $tab) {
            if (!$tab->isDisabled($order)) {
                return $tab->getId();
            }
        }

        return null;
    }

    #[Route(
        path: '/order/edit/{id}/tab/{tabId}',
        requirements: ['id' => '\d+', 'tabId' => '[A-Za-z0-9_]+'],
        methods: ['GET'],
        condition: 'request.isXmlHttpRequest()',
        name: 'admin_order_detail_tab_content',
    )]
    #[CanView]
    public function tabContentAction(int $id, string $tabId): Response
    {
        $order = $this->orderFacade->getById($id);
        $tab = $this->orderDetailTabRegistry->findTabById($order, $tabId);

        if ($tab === null || $tab->isDisabled($order)) {
            throw $this->createNotFoundException();
        }

        return $this->render('@ShopsysAdministration/content/order/detail/components/_tab_component.html.twig', [
            'order' => $order,
            'tab' => $tab,
        ]);
    }

    #[Route(
        path: '/order/edit/{id}/change-status/{statusId}',
        requirements: ['id' => '\d+', 'statusId' => '\d+'],
        methods: ['GET'],
        name: 'admin_order_edit_changestatus',
    )]
    #[CanEdit]
    #[CsrfProtection]
    public function changeStatusAction(int $id, int $statusId): Response
    {
        $order = $this->orderFacade->getById($id);
        $status = $this->orderStatusFacade->getById($statusId);

        if ($status->getType() === OrderStatusTypeEnum::TYPE_WITHDRAWN) {
            return $this->redirectToRoute(
                'admin_order_edit_withdrawal_status',
                [
                    'id' => $id,
                    'statusId' => $statusId,
                ],
            );
        }

        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->status = $status;
        $this->orderFacade->edit($id, $orderData);
        $this->addSuccessFlash(t('Order status has been changed.'));

        return $this->redirectToRoute('admin_order_edit', ['id' => $id]);
    }

    #[Route(
        path: '/order/edit/{id}/mark-as-paid',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
        name: 'admin_order_edit_markaspaid',
    )]
    #[CanEdit]
    #[CsrfProtection]
    public function markAsPaidAction(int $id): Response
    {
        $order = $this->orderFacade->getById($id);

        $this->orderPaidStatusFacade->markOrderAsPaid($order);
        $this->addSuccessFlash(t('Order has been marked as paid.'));

        return $this->redirectToRoute('admin_order_edit', ['id' => $id]);
    }
}
