<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NotificationBar;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class NotificationBarDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     */
    public function __construct(
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData
     */
    protected function createInstance(): NotificationBarData
    {
        return new NotificationBarData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData
     */
    public function create(): NotificationBarData
    {
        $notificationBarData = $this->createInstance();
        $this->fillNew($notificationBarData);

        return $notificationBarData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar $notificationBar
     * @return \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData
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

        $notificationBarData->image = $this->imageUploadDataFactory->createFromEntityAndType($notificationBar);

        return $notificationBarData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData $notificationBarData
     */
    protected function fillNew(NotificationBarData $notificationBarData): void
    {
        $notificationBarData->hidden = false;
        $notificationBarData->image = $this->imageUploadDataFactory->create();
    }
}
