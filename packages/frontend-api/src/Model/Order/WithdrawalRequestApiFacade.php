<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Messenger\WithdrawalRequestMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalChecker;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;

class WithdrawalRequestApiFacade
{
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly WithdrawalChecker $withdrawalChecker,
        protected readonly OrderStatusFacade $orderStatusFacade,
        protected readonly WithdrawalRequestMessageDispatcher $withdrawalRequestMessageDispatcher,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
    ) {
    }

    public function createWithdrawalRequest(Order $order, WithdrawalRequestData $withdrawalRequestData): void
    {
        $this->withdrawalChecker->checkOrderWithdrawal($order);
        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->withdrawalRequestData = $withdrawalRequestData;
        $orderData->status = $this->orderStatusFacade->getByType(OrderStatusTypeEnum::TYPE_WITHDRAWN);

        $this->orderFacade->edit($order->getId(), $orderData);

        $withdrawalRequest = $this->withdrawalRequestFacade->getConfirmedByOrder($order);
        $this->withdrawalRequestMessageDispatcher->dispatchWithdrawalCreatedMessage($withdrawalRequest->getId());
    }
}
