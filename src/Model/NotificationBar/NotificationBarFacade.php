<?php

declare(strict_types=1);

namespace App\Model\NotificationBar;

use App\Component\Image\ImageFacade;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class NotificationBarFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\NotificationBar\NotificationBarRepository
     */
    private $notificationBarRepository;

    /**
     * @var \App\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\NotificationBar\NotificationBarRepository $notificationBarRepository
     * @param \App\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        NotificationBarRepository $notificationBarRepository,
        ImageFacade $imageFacade
    ) {
        $this->em = $em;
        $this->notificationBarRepository = $notificationBarRepository;
        $this->imageFacade = $imageFacade;
    }

    /**
     * @param \App\Model\NotificationBar\NotificationBarData $notificationBarData
     */
    public function create(NotificationBarData $notificationBarData): void
    {
        $notificationBar = new NotificationBar($notificationBarData);

        $this->em->persist($notificationBar);
        $this->em->flush();

        $this->imageFacade->manageImages($notificationBar, $notificationBarData->image, null);
    }

    /**
     * @param \App\Model\NotificationBar\NotificationBar $notificationBar
     * @param \App\Model\NotificationBar\NotificationBarData $notificationBarData
     * @return \App\Model\NotificationBar\NotificationBar
     */
    public function edit(NotificationBar $notificationBar, NotificationBarData $notificationBarData): NotificationBar
    {
        $notificationBar->edit($notificationBarData);

        $this->em->flush();

        $this->imageFacade->manageImages($notificationBar, $notificationBarData->image, null);

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
     * @return \App\Model\NotificationBar\NotificationBar
     */
    public function getById(int $notificationBarId): NotificationBar
    {
        return $this->notificationBarRepository->getById($notificationBarId);
    }

    /**
     * @param int $domainId
     * @return \App\Model\NotificationBar\NotificationBar[]|null
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
