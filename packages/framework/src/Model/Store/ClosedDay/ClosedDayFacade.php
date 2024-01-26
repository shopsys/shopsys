<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\ClosedDay;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Store\Store;

class ClosedDayFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayRepository $closedDayRepository
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFactory $closedDayFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClosedDayRepository $closedDayRepository,
        protected readonly ClosedDayFactory $closedDayFactory,
    ) {
    }

    /**
     * @param int $closedDayId
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay
     */
    public function getById(int $closedDayId): ClosedDay
    {
        return $this->closedDayRepository->getById($closedDayId);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[]
     */
    public function getThisWeekClosedDaysNotExcludedForStoreIndexedByDayNumber(int $domainId, Store $store): array
    {
        return $this->closedDayRepository->getThisWeekClosedDaysNotExcludedForStoreIndexedByDayNumber($domainId, $store);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayData $closedDayData
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay
     */
    public function create(ClosedDayData $closedDayData): ClosedDay
    {
        $closedDay = $this->closedDayFactory->create($closedDayData);
        $this->em->persist($closedDay);
        $this->em->flush();

        return $closedDay;
    }

    /**
     * @param int $closedDayId
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayData $closedDayData
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay
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
