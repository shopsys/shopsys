<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\EntityIdentifierException;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\MultipleFilesNotAllowedException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormData;
use Shopsys\FrontendApiBundle\Model\Order\CreateOrderResultFactory;

class UploadedFileFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository $uploadedFileRepository
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator $uploadedFileLocator
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactoryInterface $uploadedFileFactory
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelationFactory $uploadedFileRelationFactory
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelationRepository $uploadedFileRelationRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly UploadedFileConfig $uploadedFileConfig,
        protected readonly UploadedFileRepository $uploadedFileRepository,
        protected readonly FilesystemOperator $filesystem,
        protected readonly UploadedFileLocator $uploadedFileLocator,
        protected readonly UploadedFileFactoryInterface $uploadedFileFactory,
        protected readonly UploadedFileRelationFactory $uploadedFileRelationFactory,
        protected readonly UploadedFileRelationRepository $uploadedFileRelationRepository,
    ) {
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData $uploadedFileData
     * @param string $type
     */
    public function manageFiles(
        object $entity,
        UploadedFileData $uploadedFileData,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): void {
        $uploadedFileEntityConfig = $this->uploadedFileConfig->getUploadedFileEntityConfig($entity);
        $uploadedFileTypeConfig = $uploadedFileEntityConfig->getTypeByName($type);

        $uploadedFiles = $uploadedFileData->uploadedFiles;
        $uploadedFilenames = $uploadedFileData->uploadedFilenames;
        $orderedFiles = $uploadedFileData->orderedFiles;
        $namesIndexedByFileIdAndLocale = $uploadedFileData->namesIndexedById;

        $entityName = $uploadedFileEntityConfig->getEntityName();

        $currentRelations = $this->uploadedFileRelationRepository->getRelationsForUploadedFiles(
            $entityName,
            $this->getEntityId($entity),
            $orderedFiles,
        );

        $this->updateFilesOrder($orderedFiles, $currentRelations);
        $this->updateFilenamesAndSlugs($uploadedFileData->currentFilenamesIndexedById);

        foreach ($namesIndexedByFileIdAndLocale as $fileId => $names) {
            $file = $this->getById($fileId);
            $file->setTranslatedNames($names);
        }

        $existingFilesCount = count($orderedFiles);
        $uploadedFilesCount = count($uploadedFiles);

        if ($uploadedFileTypeConfig->isMultiple()) {
            $this->uploadFiles(
                $entity,
                $entityName,
                $type,
                $uploadedFiles,
                $uploadedFilenames,
                $uploadedFileData->names,
                $existingFilesCount,
            );
        } else {
            if (count($orderedFiles) > 1) {
                array_shift($orderedFiles);
                $this->deleteFiles($entity, $orderedFiles);
            }

            $this->deleteAllUploadedFilesByEntity($entity);

            $this->uploadFile(
                $entity,
                $entityName,
                $type,
                array_pop($uploadedFiles),
                array_pop($uploadedFilenames),
                array_pop($uploadedFileData->names),
            );
        }

        $relations = $uploadedFileData->relations;

        $position = $existingFilesCount + $uploadedFilesCount;

        foreach ($relations as $relation) {
            $this->createRelation(
                $entityName,
                $entity->getId(),
                $relation,
                $position++,
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
     * @param array $namesIndexedByFileIdAndLocale
     */
    protected function uploadFile(
        object $entity,
        string $entityName,
        string $type,
        string $temporaryFilename,
        string $uploadedFilename,
        array $namesIndexedByFileIdAndLocale,
    ): void {
        $entityId = $this->getEntityId($entity);

        $newUploadedFile = $this->uploadedFileFactory->create(
            $entityName,
            $entityId,
            $type,
            $temporaryFilename,
            $uploadedFilename,
            0,
            $namesIndexedByFileIdAndLocale,
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
     * @param array $namesIndexedByFileIdAndLocale
     * @param int $existingFilesCount
     */
    protected function uploadFiles(
        object $entity,
        string $entityName,
        string $type,
        array $temporaryFilenames,
        array $uploadedFilenames,
        array $namesIndexedByFileIdAndLocale,
        int $existingFilesCount,
    ): void {
        if (count($temporaryFilenames) > 0) {
            $entityId = $this->getEntityId($entity);
            $files = $this->uploadedFileFactory->createMultiple(
                $entityName,
                $entityId,
                $type,
                $temporaryFilenames,
                $uploadedFilenames,
                $existingFilesCount,
                $namesIndexedByFileIdAndLocale,
            );

            $i = 0;

            foreach ($files as $file) {
                $this->em->persist($file);

                $position = $existingFilesCount + $i++;
                $this->createRelation($entityName, $entityId, $file, $position);
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

        $owningUploadedFiles = $this->uploadedFileRelationRepository
            ->getUploadedFileIdsByEntityNameIdAndNameAndUploadedFiles($entityName, $entityId, $uploadedFiles);

        foreach ($uploadedFiles as $uploadedFile) {
            if (!in_array($uploadedFile->getId(), $owningUploadedFiles, true)) {
                throw new FileNotFoundException(
                    sprintf(
                        'Entity "%s" with ID "%s" does not have relation to file with ID "%s"',
                        $entityName,
                        $entityId,
                        $uploadedFile->getId(),
                    ),
                );
            }
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
            $this->getEntityId($entity),
        );

        $this->deleteFiles($entity, $uploadedFiles);
    }

    /**
     * @param object $entity
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function getUploadedFilesByEntity(
        object $entity,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): array {
        return $this->uploadedFileRepository->getUploadedFilesByEntity(
            $this->uploadedFileConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $type,
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
     * @param int[] $uploadedFileIds
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function getByIds(array $uploadedFileIds): array
    {
        return $this->uploadedFileRepository->getByIds($uploadedFileIds);
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
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelation[] $relations
     */
    protected function updateFilesOrder(array $uploadedFiles, array $relations): void
    {
        $i = 0;

        $relationsIndexedByUploadedFileId = [];

        foreach ($relations as $relation) {
            $relationsIndexedByUploadedFileId[$relation->getUploadedFile()->getId()] = $relation;
        }

        foreach ($uploadedFiles as $uploadedFile) {
            $relation = $relationsIndexedByUploadedFileId[$uploadedFile->getId()];
            $relation->setPosition($i++);

            $this->em->persist($relation);
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
    public function getByIdSlugAndExtension(
        int $uploadedFileId,
        string $uploadedFileSlug,
        string $uploadedFileExtension,
    ): UploadedFile {
        return $this->uploadedFileRepository->getByIdSlugAndExtension(
            $uploadedFileId,
            $uploadedFileSlug,
            $uploadedFileExtension,
        );
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $file
     * @param int $position
     */
    protected function createRelation(string $entityName, int $entityId, UploadedFile $file, int $position): void
    {
        $relation = $this->uploadedFileRelationFactory->create($entityName, $entityId, $file, $position);
        $this->em->persist($relation);
    }
}
