<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;

class UploadedFileDeleteDoctrineListener
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        protected readonly UploadedFileConfig $uploadedFileConfig,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($entity instanceof UploadedFile) {
            $this->uploadedFileFacade->deleteFileFromFilesystem($entity);
        }
    }
}
