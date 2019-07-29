<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;

class UploadedFileDeleteDoctrineListener
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig
     */
    protected $uploadedFileConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade
     */
    protected $uploadedFileFacade;

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
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($this->uploadedFileConfig->hasUploadedFileEntityConfig($entity)) {
            $uploadedFile = $this->uploadedFileFacade->findUploadedFileByEntity($entity);
            if ($uploadedFile !== null) {
                $args->getEntityManager()->remove($uploadedFile);
            }
        } elseif ($entity instanceof UploadedFile) {
            $this->uploadedFileFacade->deleteFileFromFilesystem($entity);
        }
    }
}
