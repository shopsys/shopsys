<?php

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class PersonalDataAccessRequestRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $hash
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest|null
     */
    public function findByHashAndDomainId(string $hash, int $domainId): ?PersonalDataAccessRequest
    {
        $dateTime = new DateTime('-1 day');

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
    public function isHashUsed(string $hash): bool
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
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('pdar')
            ->from(PersonalDataAccessRequest::class, 'pdar');
    }
}
