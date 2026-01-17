<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal\Messenger;

use Exception;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalAdminMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class WithdrawalRequestMessageHandler
{
    public function __construct(
        protected readonly OrderMailFacade $orderMailFacade,
        protected readonly WithdrawalAdminMailFacade $withdrawalAdminMailFacade,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
        protected readonly LoggerInterface $logger,
        protected readonly OrderStatusFacade $orderStatusFacade,
    ) {
    }

    public function __invoke(WithdrawalRequestMessage $message): void
    {
        try {
            $withdrawalRequest = $this->withdrawalRequestFacade->getById($message->withdrawalRequestId);
            $withdrawnOrderStatus = $this->orderStatusFacade->getByType(OrderStatusTypeEnum::TYPE_WITHDRAWN);

            $this->orderMailFacade->sendEmail($withdrawalRequest->getOrder(), $withdrawnOrderStatus);
            $this->logger->info('Withdrawal request email prepared to be sent to customer', [
                'withdrawalRequestId' => $message->withdrawalRequestId,
            ]);
            $this->withdrawalAdminMailFacade->sendEmail($withdrawalRequest);
            $this->logger->info('Withdrawal request email prepared to be sent to admin', [
                'withdrawalRequestId' => $message->withdrawalRequestId,
            ]);
        } catch (Exception $exception) {
            $this->logger->error('Preparing withdrawal request emails failed', [
                'withdrawalRequestId' => $message->withdrawalRequestId,
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }
}
