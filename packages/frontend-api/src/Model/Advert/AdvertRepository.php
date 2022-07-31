<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Advert;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Advert\Advert;

class AdvertRepository
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
     * @param int $domainId
     * @param string $positionName
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function getVisibleAdvertsByPositionNameAndDomainId(int $domainId, string $positionName): array
    {
        return $this->getVisibleAdvertsByPositionNameQueryBuilder($domainId, $positionName)->getQuery()->execute();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function getVisibleAdvertsByDomainId(int $domainId): array
    {
        return $this->getVisibleAdvertsQueryBuilder($domainId)->getQuery()->execute();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<\Shopsys\FrameworkBundle\Model\Advert\Advert>
     */
    protected function getAdvertRepository()
    {
        return $this->em->getRepository(Advert::class);
    }

    /**
     * @param int $domainId
     * @param string $positionName
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getVisibleAdvertsByPositionNameQueryBuilder(int $domainId, string $positionName)
    {
        return $this->getVisibleAdvertsQueryBuilder($domainId)
            ->andWhere('a.positionName = :positionName')
            ->setParameter('positionName', $positionName);
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getVisibleAdvertsQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Advert::class, 'a')
            ->where('a.hidden = FALSE')
            ->andWhere('a.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }
}
