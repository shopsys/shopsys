<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NotificationBar;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\NotificationBar\Exception\NotificationBarNotFoundException;

class NotificationBarRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    protected function getNotificationBarRepository(): EntityRepository
    {
        return $this->em->getRepository(NotificationBar::class);
    }

    public function getById(int $notificationBarId): NotificationBar
    {
        $notificationBar = $this->getNotificationBarRepository()->find($notificationBarId);

        if ($notificationBar === null) {
            $message = 'Notification bar with ID ' . $notificationBarId . ' not found.';

            throw new NotificationBarNotFoundException($message);
        }

        return $notificationBar;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar[]|null
     */
    public function findVisibleAndValidByDomainId(int $domainId): ?array
    {
        return $this->getAllByDomainIdQueryBuilder($domainId)
            ->andWhere('nb.validityFrom IS NULL OR nb.validityFrom <= :now')
            ->andWhere('nb.validityTo IS NULL OR nb.validityTo >= :now')
            ->andWhere('nb.hidden = FALSE')
            ->setParameters([
                'domainId' => $domainId,
                'now' => $this->clock->now(),
            ])
            ->getQuery()->execute();
    }

    public function getAllByDomainIdQueryBuilderForGrid(int $domainId): QueryBuilder
    {
        return $this->getAllByDomainIdQueryBuilder($domainId)
            ->addSelect('CASE WHEN (nb.hidden = FALSE AND (nb.validityFrom IS NULL OR nb.validityFrom <= :now) AND (nb.validityTo IS NULL OR nb.validityTo > :now)) THEN TRUE ELSE FALSE END AS visibility')
            ->setParameter('now', $this->clock->now())
            ->orderBy('nb.id');
    }

    protected function getAllByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getNotificationBarRepository()->createQueryBuilder('nb')
            ->where('nb.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }
}
