<?php

declare(strict_types=1);

namespace App\Model\Store\ClosedDay;

use App\Model\Store\Store;
use Doctrine\ORM\EntityManagerInterface;

class ClosedDayFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Store\ClosedDay\ClosedDayRepository $closedDayRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClosedDayRepository $closedDayRepository,
    ) {
    }

    /**
     * @param int $closedDayId
     * @return \App\Model\Store\ClosedDay\ClosedDay
     */
    public function getById(int $closedDayId): ClosedDay
    {
        return $this->closedDayRepository->getById($closedDayId);
    }

    /**
     * @param int $domainId
     * @param \App\Model\Store\Store $store
     * @return \App\Model\Store\ClosedDay\ClosedDay[]
     */
    public function getThisWeekClosedDaysNotExcludedForStoreIndexedByDayNumber(int $domainId, Store $store): array
    {
        return $this->closedDayRepository->getThisWeekClosedDaysNotExcludedForStoreIndexedByDayNumber($domainId, $store);
    }

    /**
     * @param \App\Model\Store\ClosedDay\ClosedDayData $closedDayData
     * @return \App\Model\Store\ClosedDay\ClosedDay
     */
    public function create(ClosedDayData $closedDayData): ClosedDay
    {
        $closedDay = new ClosedDay($closedDayData);
        $this->em->persist($closedDay);
        $this->em->flush();

        return $closedDay;
    }

    /**
     * @param int $closedDayId
     * @param \App\Model\Store\ClosedDay\ClosedDayData $closedDayData
     * @return \App\Model\Store\ClosedDay\ClosedDay
     */
    public function edit(int $closedDayId, ClosedDayData $closedDayData): ClosedDay
    {
        $closedDay = $this->getById($closedDayId);
        $closedDay->edit($closedDayData);
        $this->em->flush();

        return $closedDay;
    }

    /**
     * @param int $closedDayId
     */
    public function deleteById(int $closedDayId): void
    {
        $closedDay = $this->getById($closedDayId);
        $this->em->remove($closedDay);
        $this->em->flush();
    }
}
