<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Slider\Exception\SliderItemNotFoundException;

class SliderItemRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getSliderItemRepository()
    {
        return $this->em->getRepository(SliderItem::class);
    }

    /**
     * @param int $sliderItemId
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem
     */
    public function getById($sliderItemId)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Slider\SliderItem|null $sliderItem */
        $sliderItem = $this->getSliderItemRepository()->find($sliderItemId);

        if ($sliderItem === null) {
            $message = 'Slider item with ID ' . $sliderItemId . ' not found.';

            throw new SliderItemNotFoundException($message);
        }

        return $sliderItem;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem|null
     */
    public function findById($id)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Slider\SliderItem $sliderItem */
        $sliderItem = $this->getSliderItemRepository()->find($id);

        return $sliderItem;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem[]
     */
    public function getAll()
    {
        return $this->getSliderItemRepository()->findAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem[]
     */
    public function getAllVisibleByDomainId(int $domainId): array
    {
        $dateToday = $this->clock->now()->format('Y-m-d 00:00:00');

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

    protected function getSliderItemQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('si')
            ->from(SliderItem::class, 'si');
    }
}
