<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\ClosedDay;

use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\Exception\ClosedDayNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\Store;

class ClosedDayRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
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
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[]
     */
    public function getThisWeekClosedDaysNotExcludedForStoreIndexedByDayNumber(int $domainId, Store $store): array
    {
        $beginningOfWeek = new DateTimeImmutable('this week monday', $this->domain->getDateTimeZone());
        $beginningOfNextWeek = $beginningOfWeek->add(new DateInterval('P7D'));

        /** @var \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[] $closedDays */
        $closedDays = $this
            ->getClosedDayRepository()
            ->createQueryBuilder('cd')
            ->where('cd.domainId = :domainId')
            ->andWhere(':store NOT MEMBER OF cd.excludedStores')
            ->andWhere('cd.date >= :beginningOfWeek')
            ->andWhere('cd.date < :beginningOfNextWeek')
            ->setParameter('domainId', $domainId)
            ->setParameter('store', $store)
            ->setParameter('beginningOfWeek', $beginningOfWeek)
            ->setParameter('beginningOfNextWeek', $beginningOfNextWeek)
            ->getQuery()
            ->getResult();

        $closedDaysIndexedByDayNumber = [];

        foreach ($closedDays as $closedDay) {
            $closedDaysIndexedByDayNumber[$closedDay->getDate()->format('N')] = $closedDay;
        }

        return $closedDaysIndexedByDayNumber;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getClosedDayRepository(): EntityRepository
    {
        return $this->em->getRepository(ClosedDay::class);
    }
}
