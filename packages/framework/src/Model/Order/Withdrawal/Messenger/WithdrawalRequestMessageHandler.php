<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal\Messenger;

use Exception;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalAdminMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class WithdrawalRequestMessageHandler
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalMailFacade $withdrawalMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalAdminMailFacade $withdrawalAdminMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade $withdrawalRequestFacade
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        protected readonly WithdrawalMailFacade $withdrawalMailFacade,
        protected readonly WithdrawalAdminMailFacade $withdrawalAdminMailFacade,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\Messenger\WithdrawalRequestMessage $message
     */
    public function __invoke(WithdrawalRequestMessage $message): void
    {
        try {
            $withdrawalRequest = $this->withdrawalRequestFacade->getById($message->withdrawalRequestId);

            $this->withdrawalMailFacade->sendMail($withdrawalRequest->getOrder());
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
