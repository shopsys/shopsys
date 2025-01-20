<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NotificationBar;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class NotificationBarFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarRepository $notificationBarRepository
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarFactory $notificationBarFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NotificationBarRepository $notificationBarRepository,
        protected readonly ImageFacade $imageFacade,
        protected readonly NotificationBarFactory $notificationBarFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData $notificationBarData
     */
    public function create(NotificationBarData $notificationBarData): void
    {
        $notificationBar = $this->notificationBarFactory->create($notificationBarData);

        $this->em->persist($notificationBar);
        $this->em->flush();

        $this->imageFacade->manageImages($notificationBar, $notificationBarData->image);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar $notificationBar
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData $notificationBarData
     * @return \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar
     */
    public function edit(NotificationBar $notificationBar, NotificationBarData $notificationBarData): NotificationBar
    {
        $notificationBar->edit($notificationBarData);

        $this->em->flush();

        $this->imageFacade->manageImages($notificationBar, $notificationBarData->image);

        return $notificationBar;
    }

    /**
     * @param int $notificationBarId
     */
    public function delete(int $notificationBarId): void
    {
        $notificationBar = $this->getById($notificationBarId);

        $this->em->remove($notificationBar);
        $this->em->flush();
    }

    /**
     * @param int $notificationBarId
     * @return \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar
     */
    public function getById(int $notificationBarId): NotificationBar
    {
        return $this->notificationBarRepository->getById($notificationBarId);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar[]|null
     */
    public function findVisibleAndValidByDomainId(int $domainId): ?array
    {
        return $this->notificationBarRepository->findVisibleAndValidByDomainId($domainId);
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllByDomainIdQueryBuilderForGrid(int $domainId): QueryBuilder
    {
        return $this->notificationBarRepository->getAllByDomainIdQueryBuilderForGrid($domainId);
    }
}
