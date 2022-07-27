<?php

namespace Shopsys\FrameworkBundle\Model\Localization;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Metadata\MetadataFactory;
use Prezent\Doctrine\Translatable\EventListener\TranslatableListener as PrezentTranslatableListener;

class TranslatableListener extends PrezentTranslatableListener
{
    /**
     * @param \Metadata\MetadataFactory $factory
     */
    public function __construct(MetadataFactory $factory)
    {
        parent::__construct($factory);

        // set default locale to NULL
        // (currentLocale of entities should be set by request or stay NULL)
        // @phpstan-ignore-next-line
        $this->setCurrentLocale(null);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
            Events::postLoad,
            Events::postPersist,
        ];
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->postLoad($args);
    }
}
