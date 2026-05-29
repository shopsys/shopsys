<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    ) {
    }

    #[Route(path: '/order/edit/{id}', requirements: ['id' => '\d+'], name: 'admin_order_edit')]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(int $id): Response
    {
        $order = $this->orderFacade->getById($id);

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing order - Nr. %number%', ['%number%' => $order->getNumber()]),
        );

        return $this->render('@ShopsysAdministration/content/order/detail/edit.html.twig', [
            'order' => $order,
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
        $status = $this->orderStatusFacade->getById($statusId);

        $orderData = $this->orderDataFactory->createFromOrder($this->orderFacade->getById($id));
        $orderData->status = $status;
        $this->orderFacade->edit($id, $orderData);
        $this->addSuccessFlash(t('Order status has been changed.'));

        return $this->redirectToRoute('admin_order_edit', ['id' => $id]);
    }
}
