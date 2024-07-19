<?php

declare(strict_types=1);

namespace App\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade as BaseUploadedFileFacade;

/**
 * @property \App\Component\UploadedFile\UploadedFileRepository $uploadedFileRepository
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig, \App\Component\UploadedFile\UploadedFileRepository $uploadedFileRepository, \League\Flysystem\FilesystemOperator $filesystem, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator $uploadedFileLocator, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactoryInterface $uploadedFileFactory, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelationFactory $uploadedFileRelationFactory, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelationRepository $uploadedFileRelationRepository)
 */
class UploadedFileFacade extends BaseUploadedFileFacade
{
    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData $uploadedFileData
     * @param string $type
     */
    public function manageSingleFile(
        object $entity,
        UploadedFileData $uploadedFileData,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): void {
        $uploadedFiles = $uploadedFileData->uploadedFiles;
        $uploadedFilenames = $uploadedFileData->uploadedFilenames;
        $uploadedFileEntityConfig = $this->uploadedFileConfig->getUploadedFileEntityConfig($entity);

        $this->deleteAllUploadedFilesByEntity($entity, $type);

        $this->uploadFile(
            $entity,
            $uploadedFileEntityConfig->getEntityName(),
            $type,
            array_pop($uploadedFiles),
            array_pop($uploadedFilenames),
            array_pop($uploadedFileData->names),
        );
    }
}
