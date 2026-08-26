<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\Event\PreRemoveEventArgs;

class UploadedFileDeleteDoctrineListener
{
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof UploadedFile) {
            $this->uploadedFileFacade->deleteFileFromFilesystem($entity);
        }
    }
}
