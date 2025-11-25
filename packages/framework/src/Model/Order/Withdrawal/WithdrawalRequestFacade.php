<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalException;

class WithdrawalRequestFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFactory $withdrawalRequestFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalChecker $withdrawalChecker
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestRepository $withdrawalRequestRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly WithdrawalRequestFactory $withdrawalRequestFactory,
        protected readonly WithdrawalChecker $withdrawalChecker,
        protected readonly WithdrawalRequestRepository $withdrawalRequestRepository,
    ) {
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
     * @param int $withdrawalRequestId
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData $withdrawalRequestData
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest
     */
    public function edit(int $withdrawalRequestId, WithdrawalRequestData $withdrawalRequestData): WithdrawalRequest
    {
        $withdrawalRequest = $this->withdrawalRequestRepository->getById($withdrawalRequestId);

        $withdrawalRequest->edit($withdrawalRequestData);

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

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest
     */
    public function getById(int $id): WithdrawalRequest
    {
        return $this->withdrawalRequestRepository->getById($id);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return bool
     */
    public function canRequestWithdrawal(Order $order): bool
    {
        try {
            $this->withdrawalChecker->checkOrderWithdrawal($order);

            return true;
        } catch (WithdrawalException) {
            return false;
        }
    }
}
