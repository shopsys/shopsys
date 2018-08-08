<?php

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class DoctrineListener
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload
     */
    private $fileUpload;

    public function __construct(FileUpload $fileUpload)
    {
        $this->fileUpload = $fileUpload;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof EntityFileUploadInterface) {
            $this->fileUpload->preFlushEntity($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof EntityFileUploadInterface) {
            $this->fileUpload->preFlushEntity($entity);
        }
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof EntityFileUploadInterface) {
            $this->fileUpload->postFlushEntity($entity);
        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof EntityFileUploadInterface) {
            $this->fileUpload->postFlushEntity($entity);
        }
    }
}
