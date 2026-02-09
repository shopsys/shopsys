<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;

class PersonalDataAccessRequestRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function findByHashAndDomainId(
        string $hash,
        int $domainId,
    ): ?PersonalDataAccessRequest {
        $dateTime = $this->clock->now()->modify('-1 day');

        return $this->getQueryBuilder()
            ->where('pdar.hash = :hash')
            ->andWhere('pdar.domainId = :domainId')
            ->andWhere('pdar.createdAt >= :createdAt')
            ->setParameters([
                'domainId' => $domainId,
                'hash' => $hash,
                'createdAt' => $dateTime,
            ])
            ->getQuery()->getOneOrNullResult();
    }

    public function isHashUsed(string $hash): bool
    {
        return (bool)$this->getQueryBuilder()
            ->select('count(pdar)')
            ->where('pdar.hash = :hash')
            ->setParameter('hash', $hash)
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('pdar')
            ->from(PersonalDataAccessRequest::class, 'pdar');
    }
}
