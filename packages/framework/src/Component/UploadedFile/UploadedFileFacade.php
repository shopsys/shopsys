<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\EntityIdentifierException;

class UploadedFileFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig
     */
    protected $uploadedFileConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository
     */
    protected $uploadedFileRepository;

    /**
     * @var \League\Flysystem\FilesystemOperator
     */
    protected $filesystem;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator
     */
    protected $uploadedFileLocator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactoryInterface
     */
    protected $uploadedFileFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository $uploadedFileRepository
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator $uploadedFileLocator
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactoryInterface $uploadedFileFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        UploadedFileConfig $uploadedFileConfig,
        UploadedFileRepository $uploadedFileRepository,
        FilesystemOperator $filesystem,
        UploadedFileLocator $uploadedFileLocator,
        UploadedFileFactoryInterface $uploadedFileFactory
    ) {
        $this->em = $em;
        $this->uploadedFileConfig = $uploadedFileConfig;
        $this->uploadedFileRepository = $uploadedFileRepository;
        $this->filesystem = $filesystem;
        $this->uploadedFileLocator = $uploadedFileLocator;
        $this->uploadedFileFactory = $uploadedFileFactory;
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData $uploadedFileData
     * @param string $type
     */
    public function manageFiles(object $entity, UploadedFileData $uploadedFileData, string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME): void
    {
        $uploadedFileEntityConfig = $this->uploadedFileConfig->getUploadedFileEntityConfig($entity);
        $uploadedFileTypeConfig = $uploadedFileEntityConfig->getTypeByName($type);

        $uploadedFiles = $uploadedFileData->uploadedFiles;
        $uploadedFilenames = $uploadedFileData->uploadedFilenames;
        $orderedFiles = $uploadedFileData->orderedFiles;

        $this->updateFilesOrder($orderedFiles);
        $this->updateFilenamesAndSlugs($uploadedFileData->currentFilenamesIndexedById);

        if ($uploadedFileTypeConfig->isMultiple()) {
            $this->uploadFiles(
                $entity,
                $uploadedFileEntityConfig->getEntityName(),
                $type,
                $uploadedFiles,
                $uploadedFilenames,
                count($orderedFiles)
            );
        } else {
            if (count($orderedFiles) > 1) {
                array_shift($orderedFiles);
                $this->deleteFiles($entity, $orderedFiles);
            }

            $this->deleteAllUploadedFilesByEntity($entity);

            $this->uploadFile(
                $entity,
                $uploadedFileEntityConfig->getEntityName(),
                $type,
                array_pop($uploadedFiles),
                array_pop($uploadedFilenames)
            );
        }

        $this->deleteFiles($entity, $uploadedFileData->filesToDelete);
    }

    /**
     * @param object $entity
     * @param string $entityName
     * @param string $type
     * @param string $temporaryFilename
     * @param string $uploadedFilename
     */
    protected function uploadFile(object $entity, string $entityName, string $type, string $temporaryFilename, string $uploadedFilename): void
    {
        $entityId = $this->getEntityId($entity);

        $newUploadedFile = $this->uploadedFileFactory->create(
            $entityName,
            $entityId,
            $type,
            $temporaryFilename,
            $uploadedFilename
        );

        $this->em->persist($newUploadedFile);
        $this->em->flush();
    }

    /**
     * @param object $entity
     * @param string $entityName
     * @param string $type
     * @param array $temporaryFilenames
     * @param array $uploadedFilenames
     * @param int $existingFilesCount
     */
    protected function uploadFiles(object $entity, string $entityName, string $type, array $temporaryFilenames, array $uploadedFilenames, int $existingFilesCount): void
    {
        if (count($temporaryFilenames) > 0) {
            $entityId = $this->getEntityId($entity);
            $files = $this->uploadedFileFactory->createMultiple(
                $entityName,
                $entityId,
                $type,
                $temporaryFilenames,
                $uploadedFilenames,
                $existingFilesCount
            );

            foreach ($files as $file) {
                $this->em->persist($file);
            }

            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     */
    public function deleteFileFromFilesystem(UploadedFile $uploadedFile): void
    {
        $filepath = $this->uploadedFileLocator->getAbsoluteUploadedFileFilepath($uploadedFile);

        if ($this->filesystem->has($filepath)) {
            $this->filesystem->delete($filepath);
        }
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $uploadedFiles
     */
    public function deleteFiles(object $entity, array $uploadedFiles): void
    {
        $entityName = $this->uploadedFileConfig->getEntityName($entity);
        $entityId = $this->getEntityId($entity);

        foreach ($uploadedFiles as $uploadedFile) {
            $uploadedFile->checkForDelete($entityName, $entityId);
        }

        foreach ($uploadedFiles as $uploadedFile) {
            $this->em->remove($uploadedFile);
        }

        $this->em->flush();
    }

    /**
     * @param object $entity
     */
    public function deleteAllUploadedFilesByEntity(object $entity): void
    {
        $uploadedFiles = $this->uploadedFileRepository->getAllUploadedFilesByEntity(
            $this->uploadedFileConfig->getEntityName($entity),
            $this->getEntityId($entity)
        );

        $this->deleteFiles($entity, $uploadedFiles);
    }

    /**
     * @param object $entity
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function getUploadedFilesByEntity(object $entity, string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME): array
    {
        return $this->uploadedFileRepository->getUploadedFilesByEntity(
            $this->uploadedFileConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $type
        );
    }

    /**
     * @param object $entity
     * @return int
     */
    protected function getEntityId(object $entity): int
    {
        $entityMetadata = $this->em->getClassMetadata(get_class($entity));
        $identifier = $entityMetadata->getIdentifierValues($entity);

        if (count($identifier) === 1) {
            return array_pop($identifier);
        }

        $message = 'Entity "' . get_class($entity) . '" has not set primary key or primary key is compound."';

        throw new EntityIdentifierException($message);
    }

    /**
     * @param int $uploadedFileId
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function getById(int $uploadedFileId): UploadedFile
    {
        return $this->uploadedFileRepository->getById($uploadedFileId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getAbsoluteUploadedFileFilepath(UploadedFile $uploadedFile): string
    {
        return $this->uploadedFileLocator->getAbsoluteUploadedFileFilepath($uploadedFile);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getUploadedFileUrl(DomainConfig $domainConfig, UploadedFile $uploadedFile): string
    {
        return $this->uploadedFileLocator->getUploadedFileUrl($domainConfig, $uploadedFile);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $uploadedFiles
     */
    protected function updateFilesOrder(array $uploadedFiles): void
    {
        $i = 0;

        foreach ($uploadedFiles as $uploadedFile) {
            $uploadedFile->setPosition($i++);
        }

        $this->em->flush();
    }

    /**
     * @param array $fileNamesIndexedByFileId
     */
    protected function updateFilenamesAndSlugs(array $fileNamesIndexedByFileId): void
    {
        foreach ($fileNamesIndexedByFileId as $fileId => $fileName) {
            $file = $this->getById($fileId);

            $file->setNameAndSlug($fileName);

            $this->em->flush();
        }
    }

    /**
     * @param int $uploadedFileId
     * @param string $uploadedFileSlug
     * @param string $uploadedFileExtension
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function getByIdSlugAndExtension(int $uploadedFileId, string $uploadedFileSlug, string $uploadedFileExtension): UploadedFile
    {
        return $this->uploadedFileRepository->getByIdSlugAndExtension(
            $uploadedFileId,
            $uploadedFileSlug,
            $uploadedFileExtension
        );
    }
}
