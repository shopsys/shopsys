<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\ReturnHash;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Order\Order;

class PaymentReturnHashFacade
{
    protected const int HASH_LENGTH = 64;
    protected const string HASH_TTL_MODIFIER = '+30 minutes';

    public function __construct(
        protected readonly PaymentReturnHashRepository $paymentReturnHashRepository,
        protected readonly PaymentReturnHashFactory $paymentReturnHashFactory,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly HashGenerator $hashGenerator,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function createForOrderAndGetHash(Order $order): string
    {
        do {
            $hash = $this->hashGenerator->generateHash(static::HASH_LENGTH);
        } while ($this->paymentReturnHashRepository->existsByHash($hash));

        $paymentReturnHash = $this->paymentReturnHashFactory->create(
            $hash,
            $order,
            $this->clock->now()->modify(static::HASH_TTL_MODIFIER),
        );

        $this->entityManager->persist($paymentReturnHash);
        $this->entityManager->flush();

        return $hash;
    }

    public function findValidByHash(string $hash): ?PaymentReturnHash
    {
        return $this->paymentReturnHashRepository->findValidByHash($hash);
    }

    public function deleteAllExpired(): void
    {
        $this->paymentReturnHashRepository->deleteAllExpired();
    }
}
