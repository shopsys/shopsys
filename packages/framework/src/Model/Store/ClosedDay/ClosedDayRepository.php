<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\ClosedDay;

use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\Exception\ClosedDayNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\Store;

class ClosedDayRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     * @param \Psr\Clock\ClockInterface $clock
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param int $closedDayId
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay
     */
    public function getById(int $closedDayId): ClosedDay
    {
        $closedDay = $this->getClosedDayRepository()->find($closedDayId);

        if ($closedDay === null) {
            throw new ClosedDayNotFoundException(sprintf('Holiday / internal day with ID %s not found.', $closedDay));
        }

        return $closedDay;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
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
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getClosedDayRepository(): EntityRepository
    {
        return $this->em->getRepository(ClosedDay::class);
    }
}
