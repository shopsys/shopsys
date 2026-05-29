<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent;

use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    name: 'OrderDetail:SummaryBar',
    template: '@ShopsysAdministration/content/order/detail/components/summary_bar.html.twig',
)]
#[ForRole(AdminRoleConstant::ROLE_ORDER)]
class SummaryBarComponent
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $orderId;

    protected ?Order $order = null;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]|null
     */
    protected ?array $orderStatuses = null;

    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderStatusFacade $orderStatusFacade,
    ) {
    }

    public function mount(Order $order): void
    {
        $this->order = $order;
        $this->orderId = $order->getId();
    }

    public function getOrder(): Order
    {
        return $this->order ??= $this->orderFacade->getById($this->orderId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]
     */
    public function getOrderStatuses(): array
    {
        return $this->orderStatuses ??= $this->orderStatusFacade->getAll();
    }

    #[LiveListener(SectionEditorFormComponent::ORDER_DETAIL_SECTION_UPDATED_EVENT)]
    #[CanView]
    public function refresh(): void
    {
        $this->order = null;
        $this->orderStatuses = null;
    }
}
