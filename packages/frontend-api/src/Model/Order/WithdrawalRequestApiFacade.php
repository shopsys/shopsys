<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalConfirmationMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalConfirmationHashInvalidException;
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
        protected readonly WithdrawalConfirmationMailFacade $withdrawalConfirmationMailFacade,
        protected readonly Domain $domain,
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

    public function requestWithdrawalConfirmation(Order $order, WithdrawalRequestData $withdrawalRequestData): void
    {
        $this->withdrawalChecker->checkOrderWithdrawal($order);

        $withdrawalRequest = $this->withdrawalRequestFacade->createUnconfirmed(
            $order,
            $withdrawalRequestData,
        );

        $this->withdrawalConfirmationMailFacade->sendMail($withdrawalRequest);
    }

    public function confirmWithdrawalRequest(string $confirmationHash): Order
    {
        $withdrawalRequest = $this->withdrawalRequestFacade->findValidUnconfirmedByConfirmationHash($confirmationHash);

        if ($withdrawalRequest === null) {
            throw new WithdrawalConfirmationHashInvalidException('Withdrawal confirmation hash is invalid or expired');
        }

        $order = $withdrawalRequest->getOrder();

        if ($order->getDomainId() !== $this->domain->getId()) {
            throw new WithdrawalConfirmationHashInvalidException('Withdrawal confirmation hash is invalid or expired');
        }

        $this->withdrawalChecker->checkOrderWithdrawal($order);
        $this->withdrawalRequestFacade->confirm($withdrawalRequest);

        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->status = $this->orderStatusFacade->getByType(OrderStatusTypeEnum::TYPE_WITHDRAWN);

        $this->orderFacade->edit($order->getId(), $orderData);

        $this->withdrawalRequestMessageDispatcher->dispatchWithdrawalCreatedMessage($withdrawalRequest->getId());

        return $order;
    }
}
