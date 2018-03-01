<?php

namespace Shopsys\ShopBundle\Model\Gdpr;

class PersonalDataAccessRequestRepository
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $hash
     * @return \Shopsys\ShopBundle\Model\Gdpr\PersonalDataAccessRequest|null
     */
    public function findByHashAndDomainId($hash, $domainId)
    {
        $date = new \DateTime();
        $date->modify('-1 day');

        return $this->getQueryBuilder()
            ->where('g.hash = :hash')
            ->andWhere('g.domainId = :domain')
            ->andWhere('g.createdAt >= :date')
            ->setParameters([
                'domain' => $domainId,
                'hash' => $hash,
                'date' => $date,
            ])
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $hash
     * @return int
     */
    public function isHashUsed($hash)
    {
        return $this->getQueryBuilder()
            ->select('count(g)')
            ->where('g.hash = :hash')
            ->setParameter('hash', $hash)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->select('g')
            ->from(PersonalDataAccessRequest::class, 'g');
    }
}
