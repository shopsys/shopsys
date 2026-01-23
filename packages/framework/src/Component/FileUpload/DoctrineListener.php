<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class DoctrineListener
{
    public function __construct(protected readonly FileUpload $fileUpload)
    {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof EntityFileUploadInterface) {
            $this->fileUpload->preFlushEntity($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof EntityFileUploadInterface) {
            $this->fileUpload->preFlushEntity($entity);
        }
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof EntityFileUploadInterface) {
            $this->fileUpload->postFlushEntity($entity);
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof EntityFileUploadInterface) {
            $this->fileUpload->postFlushEntity($entity);
        }
    }
}
