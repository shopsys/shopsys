<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalRequestNotFoundException;

class WithdrawalRequestRepository
{
    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {
    }

    protected function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(WithdrawalRequest::class);
    }

    public function findConfirmedByOrder(Order $order): ?WithdrawalRequest
    {
        return $this->getRepository()->findOneBy([
            'order' => $order,
            'confirmed' => true,
        ]);
    }

    public function findIncludingUnconfirmedByOrder(Order $order): ?WithdrawalRequest
    {
        return $this->getRepository()->findOneBy(['order' => $order]);
    }

    public function findUnconfirmedByConfirmationHashAndRequestedAfter(
        string $confirmationHash,
        DateTimeImmutable $requestedAfter,
    ): ?WithdrawalRequest {
        return $this->getRepository()->createQueryBuilder('wr')
            ->where('wr.confirmationHash = :confirmationHash')
            ->andWhere('wr.confirmed = FALSE')
            ->andWhere('wr.requestedAt > :requestedAfter')
            ->setParameter('confirmationHash', $confirmationHash)
            ->setParameter('requestedAfter', $requestedAfter)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function existsByConfirmationHash(string $confirmationHash): bool
    {
        return $this->getRepository()->count(['confirmationHash' => $confirmationHash]) > 0;
    }

    public function getConfirmedByOrder(Order $order): WithdrawalRequest
    {
        $withdrawalRequest = $this->findConfirmedByOrder($order);

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
