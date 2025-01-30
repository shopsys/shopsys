<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NotificationBar;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class NotificationBarFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData $notificationBarData
     * @return \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar
     */
    public function create(NotificationBarData $notificationBarData): NotificationBar
    {
        $entityClassName = $this->entityNameResolver->resolve(NotificationBar::class);

        return new $entityClassName($notificationBarData);
    }
}
