<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\ClosedDay;

use DateInterval;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\Exception\ClosedDayNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\Store;

class ClosedDayRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function getById(int $closedDayId): ClosedDay
    {
        $closedDay = $this->getClosedDayRepository()->find($closedDayId);

        if ($closedDay === null) {
            throw new ClosedDayNotFoundException(sprintf('Holiday / internal day with ID %s not found.', $closedDay));
        }

        return $closedDay;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[]
     */
    public function getFollowingWeekClosedDaysNotExcludedForStore(Store $store): array
    {
        return $this
            ->createFollowingWeekClosedDaysQueryBuilder($store->getDomainId())
            ->andWhere(':store NOT MEMBER OF cd.excludedStores')
            ->setParameter('store', $store)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \DateTimeInterface[]
     */
    public function getPublicHolidays(
        int $domainId,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
    ): array {
        $closedDays = $this
            ->createPublicHolidaysQueryBuilder($domainId, $startDate, $endDate)
            ->select('cd.date')
            ->getQuery()
            ->getResult();

        return array_column($closedDays, 'date');
    }

    public function hasPublicHolidays(
        int $domainId,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
    ): bool {
        $result = $this
            ->createPublicHolidaysQueryBuilder($domainId, $startDate, $endDate)
            ->select('1')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }

    protected function createPublicHolidaysQueryBuilder(
        int $domainId,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
    ): QueryBuilder {
        return $this
            ->getClosedDayRepository()
            ->createQueryBuilder('cd')
            ->where('cd.domainId = :domainId')
            ->andWhere('cd.isPublicHoliday = :isPublicHoliday')
            ->andWhere('cd.date >= :startDate')
            ->andWhere('cd.date <= :endDate')
            ->setParameter('domainId', $domainId)
            ->setParameter('isPublicHoliday', true)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store[] $stores
     * @return array<int, \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[]>
     */
    public function getFollowingWeekClosedDaysForStoresIndexedByStoreId(int $domainId, array $stores): array
    {
        if ($stores === []) {
            return [];
        }

        $closedDays = $this
            ->createFollowingWeekClosedDaysQueryBuilder($domainId)
            ->addSelect('es')
            ->leftJoin('cd.excludedStores', 'es')
            ->getQuery()
            ->getResult();

        return $this->groupClosedDaysByStoreWithExclusions($stores, $closedDays);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store[] $stores
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[] $closedDays
     * @return array<int, \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[]>
     */
    protected function groupClosedDaysByStoreWithExclusions(array $stores, array $closedDays): array
    {
        $excludedIdsByClosedDay = [];

        foreach ($closedDays as $closedDay) {
            $excludedIdsByClosedDay[$closedDay->getId()] = array_map(
                static fn (Store $store) => $store->getId(),
                $closedDay->getExcludedStores(),
            );
        }

        $result = [];

        foreach ($stores as $store) {
            $result[$store->getId()] = array_filter(
                $closedDays,
                static fn (ClosedDay $closedDay) => !in_array($store->getId(), $excludedIdsByClosedDay[$closedDay->getId()], true),
            );
        }

        return $result;
    }

    protected function createFollowingWeekClosedDaysQueryBuilder(int $domainId): QueryBuilder
    {
        $today = $this->clock->now()
            ->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId))
            ->setTime(0, 0, 0);
        $endOfFollowingWeek = $today->add(new DateInterval('P7D'));

        return $this
            ->getClosedDayRepository()
            ->createQueryBuilder('cd')
            ->where('cd.domainId = :domainId')
            ->andWhere('cd.date >= :today')
            ->andWhere('cd.date < :endOfFollowingWeek')
            ->setParameter('domainId', $domainId)
            ->setParameter('today', $today)
            ->setParameter('endOfFollowingWeek', $endOfFollowingWeek);
    }

    protected function getClosedDayRepository(): EntityRepository
    {
        return $this->em->getRepository(ClosedDay::class);
    }
}
