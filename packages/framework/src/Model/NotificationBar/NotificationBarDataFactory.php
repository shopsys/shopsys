<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NotificationBar;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class NotificationBarDataFactory
{
    public function __construct(
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    protected function createInstance(): NotificationBarData
    {
        return new NotificationBarData();
    }

    public function create(): NotificationBarData
    {
        $notificationBarData = $this->createInstance();
        $this->fillNew($notificationBarData);

        return $notificationBarData;
    }

    public function createFromNotificationBar(NotificationBar $notificationBar): NotificationBarData
    {
        $notificationBarData = $this->createInstance();

        $notificationBarData->uuid = $notificationBar->getUuid();
        $notificationBarData->domainId = $notificationBar->getDomainId();
        $notificationBarData->text = $notificationBar->getText();
        $notificationBarData->validityFrom = $notificationBar->getValidityFrom();
        $notificationBarData->validityTo = $notificationBar->getValidityTo();
        $notificationBarData->rgbColor = $notificationBar->getRgbColor();
        $notificationBarData->hidden = $notificationBar->isHidden();

        $notificationBarData->image = $this->imageUploadDataFactory->createFromEntityAndType($notificationBar);

        return $notificationBarData;
    }

    protected function fillNew(NotificationBarData $notificationBarData): void
    {
        $notificationBarData->hidden = false;
        $notificationBarData->image = $this->imageUploadDataFactory->create();
    }
}
