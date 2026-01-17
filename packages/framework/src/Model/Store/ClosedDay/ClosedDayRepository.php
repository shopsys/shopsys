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
        $today = $this->clock->now()
            ->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($store->getDomainId()))
            ->setTime(0, 0, 0);
        $endOfFollowingWeek = $today->add(new DateInterval('P7D'))->format('Y-M-d');

        return $this
            ->getClosedDayRepository()
            ->createQueryBuilder('cd')
            ->where('cd.domainId = :domainId')
            ->andWhere(':store NOT MEMBER OF cd.excludedStores')
            ->andWhere('cd.date >= :today')
            ->andWhere('cd.date < :endOfFollowingWeek')
            ->setParameter('domainId', $store->getDomainId())
            ->setParameter('store', $store)
            ->setParameter('today', $today)
            ->setParameter('endOfFollowingWeek', $endOfFollowingWeek)
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

    protected function getClosedDayRepository(): EntityRepository
    {
        return $this->em->getRepository(ClosedDay::class);
    }
}
