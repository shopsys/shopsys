<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NotificationBar;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\NotificationBar\Exception\NotificationBarNotFoundException;

class NotificationBarRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getNotificationBarRepository(): EntityRepository
    {
        return $this->em->getRepository(NotificationBar::class);
    }

    /**
     * @param int $notificationBarId
     * @return \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar
     */
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
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar[]|null
     */
    public function findVisibleAndValidByDomainId(int $domainId): ?array
    {
        $dateTodayMidnight = new DateTime();
        $dateTodayMidnight = $dateTodayMidnight->format('Y-m-d 00:00:00');

        return $this->getAllByDomainIdQueryBuilder($domainId)
            ->andWhere('nb.validityFrom IS NULL OR nb.validityFrom <= :now')
            ->andWhere('nb.validityTo IS NULL OR nb.validityTo >= :now')
            ->andWhere('nb.hidden = FALSE')
            ->setParameters([
                'domainId' => $domainId,
                'now' => $dateTodayMidnight,
            ])
            ->getQuery()->execute();
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllByDomainIdQueryBuilderForGrid(int $domainId): QueryBuilder
    {
        return $this->getAllByDomainIdQueryBuilder($domainId)
            ->addSelect('CASE WHEN (nb.hidden = FALSE AND (nb.validityFrom IS NULL OR nb.validityFrom <= :now) AND (nb.validityTo IS NULL OR nb.validityTo > :now)) THEN TRUE ELSE FALSE END AS visibility')
            ->setParameter('now', new DateTime())
            ->orderBy('nb.id');
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAllByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getNotificationBarRepository()->createQueryBuilder('nb')
            ->where('nb.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }
}
