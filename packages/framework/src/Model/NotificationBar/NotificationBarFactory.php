<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NotificationBar;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class NotificationBarFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(NotificationBarData $notificationBarData): NotificationBar
    {
        $entityClassName = $this->entityNameResolver->resolve(NotificationBar::class);

        return new $entityClassName($notificationBarData);
    }
}
