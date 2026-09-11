<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;

final class OrderCancellationTestHelper
{
    public function __construct(
        private readonly OrderFacade $orderFacade,
        private readonly OrderDataFactory $orderDataFactory,
        private readonly OrderStatusFacade $orderStatusFacade,
    ) {
    }

    public function cancelOrder(Order $order): void
    {
        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->status = $this->orderStatusFacade->getByType(OrderStatusTypeEnum::TYPE_CANCELED);

        $this->orderFacade->edit($order->getId(), $orderData);
    }
}
