<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;

class UploadedFileDeleteDoctrineListener
{
    protected UploadedFileConfig $uploadedFileConfig;

    protected UploadedFileFacade $uploadedFileFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        UploadedFileConfig $uploadedFileConfig,
        UploadedFileFacade $uploadedFileFacade
    ) {
        $this->uploadedFileConfig = $uploadedFileConfig;
        $this->uploadedFileFacade = $uploadedFileFacade;
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($this->uploadedFileConfig->hasUploadedFileEntityConfig($entity)) {
            $this->uploadedFileFacade->deleteAllUploadedFilesByEntity($entity);
        } elseif ($entity instanceof UploadedFile) {
            $this->uploadedFileFacade->deleteFileFromFilesystem($entity);
        }
    }
}
