<?php

declare(strict_types=1);

namespace App\Model\NotificationBar;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class NotificationBarDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     */
    public function __construct(
        private readonly ImageUploadDataFactory $imageUploadDataFactory
    ) {
    }

    /**
     * @return \App\Model\NotificationBar\NotificationBarData
     */
    private function createInstance(): NotificationBarData
    {
        $notificationBarData = new NotificationBarData();
        $notificationBarData->image = $this->imageUploadDataFactory->create();

        return $notificationBarData;
    }

    /**
     * @return \App\Model\NotificationBar\NotificationBarData
     */
    public function create(): NotificationBarData
    {
        $notificationBarData = $this->createInstance();
        $this->fillNew($notificationBarData);

        return $notificationBarData;
    }

    /**
     * @param \App\Model\NotificationBar\NotificationBar $notificationBar
     * @return \App\Model\NotificationBar\NotificationBarData
     */
    public function createFromNotificationBar(NotificationBar $notificationBar): NotificationBarData
    {
        $notificationBarData = $this->createInstance();

        $notificationBarData->domainId = $notificationBar->getDomainId();
        $notificationBarData->text = $notificationBar->getText();
        $notificationBarData->validityFrom = $notificationBar->getValidityFrom();
        $notificationBarData->validityTo = $notificationBar->getValidityTo();
        $notificationBarData->rgbColor = $notificationBar->getRgbColor();
        $notificationBarData->hidden = $notificationBar->isHidden();

        $notificationBarData->image = $this->imageUploadDataFactory->createFromEntityAndType($notificationBar, null);

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
