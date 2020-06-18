<?php

declare(strict_types=1);

namespace App\Model\Advert;

use App\Model\Category\Category;
use DateTime;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertRepository as BaseAdvertRepository;

/**
 * @method \App\Model\Advert\Advert|null findById(string $advertId)
 * @method \App\Model\Advert\Advert|null findRandomAdvertByPosition(string $positionName, int $domainId)
 * @method \App\Model\Advert\Advert getById(int $advertId)
 */
class AdvertRepository extends BaseAdvertRepository
{
    /**
     * @param string $positionName
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAdvertByPositionQueryBuilder($positionName, $domainId): QueryBuilder
    {
        $dateToday = new DateTime();
        $dateToday = $dateToday->format('Y-m-d 00:00:00');

        return $this->getAdvertQueryBuilder()
            ->where('a.positionName = :positionName')
            ->andWhere('a.hidden = FALSE')
            ->andWhere('a.domainId = :domainId')
            ->andWhere('a.datetimeVisibleFrom is NULL or a.datetimeVisibleFrom <= :now')
            ->andWhere('a.datetimeVisibleTo is NULL or a.datetimeVisibleTo >= :now')
            ->setParameters([
                'domainId' => $domainId,
                'positionName' => $positionName,
                'now' => $dateToday,
            ]);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getAdvertQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Advert::class, 'a');
    }

    /**
     * @param string $positionName
     * @param \App\Model\Category\Category $category
     * @param int $domainId
     * @return \App\Model\Advert\Advert|null
     */
    public function findRandomAdvertByPositionAndCategory(string $positionName, Category $category, int $domainId): ?Advert
    {
        $count = $this->getAdvertByPositionQueryBuilder($positionName, $domainId)
            ->select('COUNT(a)')
            ->join('a.categories', 'ac', Join::WITH, 'ac = :category')
            ->setParameter('category', $category)
            ->getQuery()->getSingleScalarResult();

        // COUNT() returns BIGINT which is hydrated into string on 32-bit architecture
        if ((int)$count === 0) {
            return null;
        }

        return $this->getAdvertByPositionQueryBuilder($positionName, $domainId)
            ->join('a.categories', 'ac', Join::WITH, 'ac = :category')
            ->setParameter('category', $category)
            ->setFirstResult(rand(0, $count - 1))
            ->setMaxResults(1)
            ->getQuery()->getSingleResult();
    }
}
