<?php

declare(strict_types=1);

namespace App\Model\Advert;

use DateTime;
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
    protected function getAdvertByPositionQueryBuilder($positionName, $domainId, $category = null): QueryBuilder
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
}
