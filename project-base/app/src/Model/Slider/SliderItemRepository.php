<?php

declare(strict_types=1);

namespace App\Model\Slider;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemRepository as BaseSliderItemRepository;

/**
 * @method \App\Model\Slider\SliderItem getById(int $sliderItemId)
 * @method \App\Model\Slider\SliderItem|null findById(int $id)
 * @method \App\Model\Slider\SliderItem[] getAll()
 */
class SliderItemRepository extends BaseSliderItemRepository
{
    /**
     * @param int $domainId
     * @return \App\Model\Slider\SliderItem[]
     */
    public function getAllVisibleByDomainId($domainId): array
    {
        $dateToday = new DateTime();
        $dateToday = $dateToday->format('Y-m-d 00:00:00');

        $queryBuilder = $this->getSliderItemQueryBuilder()
            ->where('si.domainId = :domainId')
            ->andWhere('si.hidden = :hidden')
            ->andWhere('si.datetimeVisibleFrom is NULL or si.datetimeVisibleFrom <= :now')
            ->andWhere('si.datetimeVisibleTo is NULL or si.datetimeVisibleTo >= :now')
            ->orderBy('si.position')
            ->addOrderBy('si.id');

        $queryBuilder->setParameters([
            'domainId' => $domainId,
            'hidden' => false,
            'now' => $dateToday,
        ]);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getSliderItemQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('si')
            ->from(SliderItem::class, 'si');
    }
}
