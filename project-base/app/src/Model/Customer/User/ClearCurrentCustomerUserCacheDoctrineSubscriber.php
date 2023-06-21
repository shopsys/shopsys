<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Events;

class ClearCurrentCustomerUserCacheDoctrineSubscriber implements EventSubscriber
{
    /**
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        private readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @param \Doctrine\ORM\Event\OnClearEventArgs $args
     */
    public function onClear(OnClearEventArgs $args): void
    {
        $this->currentCustomerUser->invalidateCache();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onClear,
        ];
    }
}
