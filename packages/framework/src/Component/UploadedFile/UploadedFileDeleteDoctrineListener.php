<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;

class UploadedFileDeleteDoctrineListener
{
    public function __construct(
        protected readonly UploadedFileConfig $uploadedFileConfig,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($entity instanceof UploadedFile) {
            $this->uploadedFileFacade->deleteFileFromFilesystem($entity);
        }
    }
}
