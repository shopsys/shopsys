<?php

declare(strict_types=1);

namespace App\Model\NotificationBar;

use App\Component\Image\ImageFacade;

class NotificationBarDataFactory
{
    /**
     * @var \App\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @param \App\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(ImageFacade $imageFacade)
    {
        $this->imageFacade = $imageFacade;
    }

    /**
     * @return \App\Model\NotificationBar\NotificationBarData
     */
    public function create(): NotificationBarData
    {
        $notificationBarData = new NotificationBarData();
        $this->fillNew($notificationBarData);

        return $notificationBarData;
    }

    /**
     * @param \App\Model\NotificationBar\NotificationBar $notificationBar
     * @return \App\Model\NotificationBar\NotificationBarData
     */
    public function createFromNotificationBar(NotificationBar $notificationBar): NotificationBarData
    {
        $notificationBarData = new NotificationBarData();

        $notificationBarData->domainId = $notificationBar->getDomainId();
        $notificationBarData->text = $notificationBar->getText();
        $notificationBarData->validityFrom = $notificationBar->getValidityFrom();
        $notificationBarData->validityTo = $notificationBar->getValidityTo();
        $notificationBarData->rgbColor = $notificationBar->getRgbColor();
        $notificationBarData->hidden = $notificationBar->isHidden();

        $notificationBarData->image->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($notificationBar, null);

        return $notificationBarData;
    }

    /**
     * @param \App\Model\NotificationBar\NotificationBarData $notificationBarData
     */
    private function fillNew(NotificationBarData $notificationBarData): void
    {
        $notificationBarData->hidden = false;
    }
}
