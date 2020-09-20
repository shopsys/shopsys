<?php

declare(strict_types=1);


namespace App\Model\UrlRedirect;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;

class UrlRegularRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(UrlRegular::class);
    }

    /**
     * @param string $regular
     * @param int $domainId
     * @return \App\Model\UrlRedirect\UrlRegular|null
     */
    public function findByRegularAndDomainId(string $regular, int $domainId): ?UrlRegular
    {
        return $this->getRepository()->findOneBy(['regular' => $regular, 'domainId' => $domainId]);
    }

    /**
     * @param int $domainId
     * @return string[][]
     */
    public function getAllByDomainId(int $domainId): array
    {
        $queryBuider = $this->em->createQueryBuilder();
        $results = $queryBuider->select('ur.regular, ur.newUrl  ')
            ->from(UrlRegular::class, 'ur')
            ->where('ur.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->getScalarResult()
        ;

        return $results;
    }
}
