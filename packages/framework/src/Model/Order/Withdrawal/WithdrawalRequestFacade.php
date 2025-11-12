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
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFactory $withdrawalRequestFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalCustomerMailFacade $withdrawalCustomerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalAdminMailFacade $withdrawalAdminMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalChecker $withdrawalChecker
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestRepository $withdrawalRequestRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly WithdrawalRequestFactory $withdrawalRequestFactory,
        protected readonly WithdrawalCustomerMailFacade $withdrawalCustomerMailFacade,
        protected readonly WithdrawalAdminMailFacade $withdrawalAdminMailFacade,
        protected readonly WithdrawalChecker $withdrawalChecker,
        protected readonly WithdrawalRequestRepository $withdrawalRequestRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData $withdrawalRequestData
     */
    public function createWithdrawalRequest(Order $order, WithdrawalRequestData $withdrawalRequestData): void
    {
        $this->withdrawalChecker->checkOrderWithdrawal($order);

        $withdrawalRequest = $this->createOnly($order, $withdrawalRequestData);

        $this->withdrawalCustomerMailFacade->sendEmail($withdrawalRequest);
        $this->withdrawalAdminMailFacade->sendEmail($withdrawalRequest);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData $withdrawalRequestData
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest
     */
    public function createOnly(Order $order, WithdrawalRequestData $withdrawalRequestData): WithdrawalRequest
    {
        $withdrawalRequest = $this->withdrawalRequestFactory->create($order, $withdrawalRequestData);

        $this->em->persist($withdrawalRequest);
        $this->em->flush();

        return $withdrawalRequest;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest|null
     */
    public function findByOrder(Order $order): ?WithdrawalRequest
    {
        return $this->withdrawalRequestRepository->findByOrder($order);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest
     */
    public function getByOrder(Order $order): WithdrawalRequest
    {
        return $this->withdrawalRequestRepository->getByOrder($order);
    }
}
