<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;

class PersonalDataAccessRequestRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param string $hash
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest|null
     */
    public function findByHashAndDomainId($hash, $domainId)
    {
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

    /**
     * @param string $hash
     * @return bool
     */
    public function isHashUsed($hash)
    {
        return (bool)$this->getQueryBuilder()
            ->select('count(pdar)')
            ->where('pdar.hash = :hash')
            ->setParameter('hash', $hash)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->select('pdar')
            ->from(PersonalDataAccessRequest::class, 'pdar');
    }
}
