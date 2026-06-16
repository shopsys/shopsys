<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\ReturnHash;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Clock\ClockInterface;

class PaymentReturnHashRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function findValidByHash(string $hash): ?PaymentReturnHash
    {
        return $this->getRepository()->createQueryBuilder('prh')
            ->where('prh.hash = :hash')
            ->andWhere('prh.expiresAt > :now')
            ->setParameter('hash', $hash)
            ->setParameter('now', $this->clock->now())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function existsByHash(string $hash): bool
    {
        return $this->getRepository()->count(['hash' => $hash]) > 0;
    }

    public function deleteAllExpired(): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(PaymentReturnHash::class, 'prh')
            ->where('prh.expiresAt <= :now')
            ->setParameter('now', $this->clock->now())
            ->getQuery()
            ->execute();
    }

    protected function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(PaymentReturnHash::class);
    }
}
