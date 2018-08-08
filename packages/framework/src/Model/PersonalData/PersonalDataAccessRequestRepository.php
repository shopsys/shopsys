<?php

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class PersonalDataAccessRequestRepository
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function findByHashAndDomainId(string $hash, int $domainId): ?\Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest
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
    
    public function isHashUsed(string $hash): bool
    {
        return (bool)$this->getQueryBuilder()
            ->select('count(pdar)')
            ->where('pdar.hash = :hash')
            ->setParameter('hash', $hash)
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function getQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('pdar')
            ->from(PersonalDataAccessRequest::class, 'pdar');
    }
}
