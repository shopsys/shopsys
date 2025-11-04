<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalAdminMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalCustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;

class WithdrawalRequestFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory $withdrawalRequestDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalCustomerMailFacade $withdrawalCustomerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalAdminMailFacade $withdrawalAdminMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalChecker $withdrawalChecker
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly WithdrawalRequestDataFactory $withdrawalRequestDataFactory,
        protected readonly WithdrawalCustomerMailFacade $withdrawalCustomerMailFacade,
        protected readonly WithdrawalAdminMailFacade $withdrawalAdminMailFacade,
        protected readonly WithdrawalChecker $withdrawalChecker,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param array<string, mixed> $withdrawalData
     */
    public function createWithdrawalRequest(Order $order, array $withdrawalData): void
    {
        $this->withdrawalChecker->checkOrderWithdrawal($order);

        $withdrawalRequestData = $this->withdrawalRequestDataFactory->createFromArray($withdrawalData);

        $order->setWithdrawalData($withdrawalRequestData);

        $this->em->flush();

        $this->withdrawalCustomerMailFacade->sendEmail($order);
        $this->withdrawalAdminMailFacade->sendEmail($order);
    }
}
