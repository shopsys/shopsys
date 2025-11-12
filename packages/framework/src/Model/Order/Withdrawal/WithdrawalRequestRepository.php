<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalRequestNotFoundException;

class WithdrawalRequestRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return \Doctrine\Persistence\ObjectRepository
     */
    protected function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(WithdrawalRequest::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest|null
     */
    public function findByOrder(Order $order): ?WithdrawalRequest
    {
        return $this->getRepository()->findOneBy(['order' => $order]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest
     */
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
}
