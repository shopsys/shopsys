<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Advert\Exception\AdvertNotFoundException;
use Shopsys\FrameworkBundle\Model\Category\Category;

class AdvertRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    protected function getAdvertRepository(): EntityRepository
    {
        return $this->em->getRepository(Advert::class);
    }

    public function findById(int $advertId): ?Advert
    {
        return $this->getAdvertRepository()->find($advertId);
    }

    /**
     * @param string[] $positionNames
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

    public function getVisibleAdvertsQueryBuilder(int $domainId): QueryBuilder
    {
        $dateToday = $this->clock->now()->format('Y-m-d 00:00:00');

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
