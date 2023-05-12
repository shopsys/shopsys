<?php

namespace Shopsys\FrameworkBundle\Component\Setting;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Events;

class ClearSettingsCacheDoctrineSubscriber implements EventSubscriber
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(protected readonly Setting $setting)
    {
    }

    /**
     * @param \Doctrine\ORM\Event\OnClearEventArgs $args
     */
    public function onClear(OnClearEventArgs $args): void
    {
        $class = $args->getEntityClass();

        if (
            $args->clearsAllEntities()
            || $class === SettingValue::class
            || is_subclass_of($class, SettingValue::class)
        ) {
            $this->setting->clearCache();
        }
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
