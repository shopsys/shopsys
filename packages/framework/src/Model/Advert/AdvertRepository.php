<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Shopsys\FrameworkBundle\Model\Advert\Exception\AdvertNotFoundException;
use Shopsys\FrameworkBundle\Model\Category\Category;

class AdvertRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAdvertRepository(): EntityRepository
    {
        return $this->em->getRepository(Advert::class);
    }

    /**
     * @param int $advertId
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert|null
     */
    public function findById(int $advertId): ?Advert
    {
        return $this->getAdvertRepository()->find($advertId);
    }

    /**
     * @param string[] $positionNames
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $category
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVisibleAdvertByPositionsQueryBuilder(
        array $positionNames,
        int $domainId,
        ?Category $category = null,
    ): QueryBuilder {
        if ($category === null) {
            foreach ($positionNames as $positionName) {
                if (AdvertPositionRegistry::isCategoryPosition($positionName)) {
                    throw new LogicException('Cannot retrieve advert on product list page without setting category.');
                }
            }
        }

        $queryBuilder = $this->getVisibleAdvertsQueryBuilder($domainId)
            ->andWhere('a.positionName IN (:positionNames)')
            ->setParameter('positionNames', $positionNames);

        if ($category !== null) {
            $queryBuilder
                ->join('a.categories', 'c')
                ->andWhere('c = :category')
                ->setParameter('category', $category);
        }

        return $queryBuilder;
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVisibleAdvertsQueryBuilder(int $domainId): QueryBuilder
    {
        $dateToday = (new DateTimeImmutable())->format('Y-m-d 00:00:00');

        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Advert::class, 'a')
            ->where('a.hidden = FALSE')
            ->andWhere('a.datetimeVisibleFrom is NULL or a.datetimeVisibleFrom <= :now')
            ->andWhere('a.datetimeVisibleTo is NULL or a.datetimeVisibleTo >= :now')
            ->andWhere('a.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->setParameter('now', $dateToday);
    }

    /**
     * @param string $positionName
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $category
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert|null
     */
    public function findRandomAdvertByPosition(
        string $positionName,
        int $domainId,
        ?Category $category = null,
    ): ?Advert {
        $count = $this->getVisibleAdvertByPositionsQueryBuilder([$positionName], $domainId, $category)
            ->select('COUNT(a)')
            ->getQuery()->getSingleScalarResult();

        // COUNT() returns BIGINT which is hydrated into string on 32-bit architecture
        if ((int)$count === 0) {
            return null;
        }

        return $this->getVisibleAdvertByPositionsQueryBuilder([$positionName], $domainId, $category)
            ->setFirstResult(random_int(0, $count - 1))
            ->setMaxResults(1)
            ->getQuery()->getSingleResult();
    }

    /**
     * @param int $advertId
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert
     */
    public function getById(int $advertId): Advert
    {
        $advert = $this->getAdvertRepository()->find($advertId);

        if ($advert === null) {
            $message = 'Advert with ID ' . $advertId . ' not found';

            throw new AdvertNotFoundException($message);
        }

        return $advert;
    }
}
