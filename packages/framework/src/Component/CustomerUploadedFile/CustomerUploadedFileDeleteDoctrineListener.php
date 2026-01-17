<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig;

class CustomerUploadedFileDeleteDoctrineListener
{
    public function __construct(
        protected readonly CustomerUploadedFileConfig $customerUploadedFileConfig,
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
    ) {
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($this->customerUploadedFileConfig->hasUploadedFileEntityConfig($entity)) {
            $this->customerUploadedFileFacade->deleteAllUploadedFilesByEntity($entity);
        } elseif ($entity instanceof CustomerUploadedFile) {
            $this->customerUploadedFileFacade->deleteFileFromFilesystem($entity);
        }
    }
}
