<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalException;

class WithdrawalRequestFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly WithdrawalRequestFactory $withdrawalRequestFactory,
        protected readonly WithdrawalChecker $withdrawalChecker,
        protected readonly WithdrawalRequestRepository $withdrawalRequestRepository,
    ) {
    }

    public function createOnly(Order $order, WithdrawalRequestData $withdrawalRequestData): WithdrawalRequest
    {
        $withdrawalRequest = $this->withdrawalRequestFactory->create($order, $withdrawalRequestData);

        $this->em->persist($withdrawalRequest);
        $this->em->flush();

        return $withdrawalRequest;
    }

    public function edit(int $withdrawalRequestId, WithdrawalRequestData $withdrawalRequestData): WithdrawalRequest
    {
        $withdrawalRequest = $this->withdrawalRequestRepository->getById($withdrawalRequestId);

        $withdrawalRequest->edit($withdrawalRequestData);

        $this->em->flush();

        return $withdrawalRequest;
    }

    public function findByOrder(Order $order): ?WithdrawalRequest
    {
        return $this->withdrawalRequestRepository->findByOrder($order);
    }

    public function getByOrder(Order $order): WithdrawalRequest
    {
        return $this->withdrawalRequestRepository->getByOrder($order);
    }

    public function getById(int $id): WithdrawalRequest
    {
        return $this->withdrawalRequestRepository->getById($id);
    }

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
