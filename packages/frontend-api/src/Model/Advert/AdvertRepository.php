<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Advert;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Category\Category;

class AdvertRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param int $domainId
     * @param string $positionName
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $category
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function getVisibleAdvertsByPositionNameAndDomainId(
        int $domainId,
        string $positionName,
        ?Category $category = null,
    ): array {
        return $this->getVisibleAdvertsByPositionNameQueryBuilder($domainId, $positionName, $category)->getQuery()->execute();
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
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAdvertRepository()
    {
        return $this->em->getRepository(Advert::class);
    }

    /**
     * @param int $domainId
     * @param string $positionName
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $category
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getVisibleAdvertsByPositionNameQueryBuilder(
        int $domainId,
        string $positionName,
        ?Category $category = null,
    ) {
        $queryBuilder = $this->getVisibleAdvertsQueryBuilder($domainId)
            ->andWhere('a.positionName = :positionName')
            ->setParameter('positionName', $positionName);

        if ($category !== null) {
            $queryBuilder
                ->leftJoin('a.categories', 'c')
                ->andWhere('c IS NULL OR c = :category')
                ->setParameter('category', $category);
        }

        return $queryBuilder;
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
