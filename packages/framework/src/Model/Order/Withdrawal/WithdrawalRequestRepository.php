<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalRequestNotFoundException;

class WithdrawalRequestRepository
{
    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {
    }

    protected function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(WithdrawalRequest::class);
    }

    public function findByOrder(Order $order): ?WithdrawalRequest
    {
        return $this->getRepository()->findOneBy(['order' => $order]);
    }

    public function getByOrder(Order $order): WithdrawalRequest
    {
        $withdrawalRequest = $this->findByOrder($order);

        if ($withdrawalRequest === null) {
            throw new WithdrawalRequestNotFoundException(
                'Withdrawal request for order ID ' . $order->getId() . ' not found.',
            );
        }

        return $withdrawalRequest;
    }

    public function getById(int $id): WithdrawalRequest
    {
        $withdrawalRequest = $this->getRepository()->find($id);

        if ($withdrawalRequest === null) {
            throw new WithdrawalRequestNotFoundException(
                'Withdrawal request with ID ' . $id . ' not found.',
            );
        }

        return $withdrawalRequest;
    }
}
