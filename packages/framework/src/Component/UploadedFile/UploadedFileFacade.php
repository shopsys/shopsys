<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileLocator;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileRepositoryInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfigInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\MultipleFilesNotAllowedException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormData;

/**
 * @method \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile getById(int $uploadedFileId)
 * @method \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] getUploadedFilesByEntity(object $entity, string $type = \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig::DEFAULT_TYPE_NAME)
 */
class UploadedFileFacade extends AbstractUploadedFileFacade
{
    /**
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository $uploadedFileRepository
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator $uploadedFileLocator
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactoryInterface $uploadedFileFactory
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelationFactory $uploadedFileRelationFactory
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelationRepository $uploadedFileRelationRepository
     */
    public function __construct(
        FilesystemOperator $filesystem,
        EntityManagerInterface $em,
        protected readonly UploadedFileConfig $uploadedFileConfig,
        protected readonly UploadedFileRepository $uploadedFileRepository,
        protected readonly UploadedFileLocator $uploadedFileLocator,
        protected readonly UploadedFileFactoryInterface $uploadedFileFactory,
        protected readonly UploadedFileRelationFactory $uploadedFileRelationFactory,
        protected readonly UploadedFileRelationRepository $uploadedFileRelationRepository,
    ) {
        parent::__construct($filesystem, $em);
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

        $currentRelations = $this->uploadedFileRelationRepository->getByEntityNameAndIdAndUploadedFiles(
            $entityName,
            $this->getEntityId($entity),
            $orderedFiles,
            $type,
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
                $existingFilesCount,
                $uploadedFileData->names,
            );
        } else {
            $temporaryFilename = array_pop($uploadedFiles);

            if (count($orderedFiles) > 0) {
                $existingFile = array_shift($orderedFiles);

                if (count($orderedFiles) > 0) {
                    $this->deleteRelationsByEntityAndUploadedFiles($entity, $orderedFiles, $type);
                }

                if ($temporaryFilename) {
                    $this->deleteRelationsByEntityAndUploadedFiles($entity, [$existingFile], $type);
                }
            }

            if ($temporaryFilename) {
                $this->uploadFile(
                    $entity,
                    $entityName,
                    $type,
                    $temporaryFilename,
                    array_pop($uploadedFilenames),
                    array_pop($uploadedFileData->names) ?? [],
                );
            }
        }

        $position = $existingFilesCount + $uploadedFilesCount;
        $this->createRelations($uploadedFileData, $currentRelations, $entityName, $entity, $position, $type);

        $this->deleteRelationsByEntityAndUploadedFiles($entity, $uploadedFileData->filesToDelete, $type);
    }

    /**
     * @param object $entity
     * @param string $entityName
     * @param string $type
     * @param string $temporaryFilename
     * @param string $uploadedFilename
     * @param array<string, string> $namesIndexedByLocale
     */
    protected function uploadFile(
        object $entity,
        string $entityName,
        string $type,
        string $temporaryFilename,
        string $uploadedFilename,
        array $namesIndexedByLocale = [],
    ): void {
        $newUploadedFile = $this->uploadedFileFactory->create(
            $temporaryFilename,
            $uploadedFilename,
            $namesIndexedByLocale,
        );

        $this->createRelation($entityName, $this->getEntityId($entity), $newUploadedFile, 0, $type);

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
     * @param array<int, array<string, string>> $namesIndexedByFileIdAndLocale
     */
    public function uploadFiles(
        object $entity,
        string $entityName,
        string $type,
        array $temporaryFilenames,
        array $uploadedFilenames,
        int $existingFilesCount,
        array $namesIndexedByFileIdAndLocale = [],
    ): void {
        if (count($temporaryFilenames) > 0) {
            $entityId = $this->getEntityId($entity);
            $files = $this->uploadedFileFactory->createMultiple(
                $temporaryFilenames,
                $uploadedFilenames,
                $namesIndexedByFileIdAndLocale,
            );

            $i = 0;

            foreach ($files as $file) {
                $this->em->persist($file);

                $position = $existingFilesCount + $i++;
                $this->createRelation($entityName, $entityId, $file, $position, $type);
            }

            $this->em->flush();
        }
    }

    /**
     * @param array $temporaryFilenames
     * @param array $uploadedFilenames
     * @param array<int, array<string, string>> $namesIndexedByFileIdAndLocale
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function uploadFilesWithoutRelations(
        array $temporaryFilenames,
        array $uploadedFilenames,
        array $namesIndexedByFileIdAndLocale,
    ): array {
        if (count($temporaryFilenames) === 0) {
            return [];
        }

        $files = $this->uploadedFileFactory->createMultiple(
            $temporaryFilenames,
            $uploadedFilenames,
            $namesIndexedByFileIdAndLocale,
        );

        foreach ($files as $file) {
            $this->em->persist($file);
        }

        $this->em->flush();

        return $files;
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $uploadedFiles
     * @param string $type
     */
    public function deleteRelationsByEntityAndUploadedFiles(object $entity, array $uploadedFiles, string $type): void
    {
        $entityName = $this->uploadedFileConfig->getEntityName($entity);
        $entityId = $this->getEntityId($entity);

        $this->uploadedFileRelationRepository->deleteRelationsByEntityNameAndIdsAndUploadedFiles(
            $entityName,
            [$entityId],
            $uploadedFiles,
            $type,
        );
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
     * @param string $type
     */
    protected function createRelation(
        string $entityName,
        int $entityId,
        UploadedFile $file,
        int $position,
        string $type,
    ): void {
        $relation = $this->uploadedFileRelationFactory->create($entityName, $entityId, $file, $position, $type);
        $this->em->persist($relation);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $file
     * @param \Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormData $uploadedFileFormData
     */
    public function edit(UploadedFile $file, UploadedFileFormData $uploadedFileFormData): void
    {
        $uploadedFiles = $uploadedFileFormData->files->uploadedFiles;
        $filesCount = count($uploadedFiles);

        if ($filesCount > 1) {
            throw new MultipleFilesNotAllowedException('Too many files uploaded, only single file is expected.');
        }

        if ($filesCount === 1) {
            $replacementUploadedFile = array_pop($uploadedFiles);
            $file->setTemporaryFilename($replacementUploadedFile);
        }

        $file->setTranslatedNames($uploadedFileFormData->names);
        $file->setNameAndSlug($uploadedFileFormData->name);

        $this->updateRelationsForUploadedFileByEntities($file, Product::class, $uploadedFileFormData->products, UploadedFileTypeConfig::DEFAULT_TYPE_NAME);

        $this->em->persist($file);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     */
    public function deleteFile(UploadedFile $uploadedFile): void
    {
        $this->em->remove($uploadedFile);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @param string $entityClass
     * @param string $type
     * @return int[]
     */
    public function getEntityIdsForUploadedFile(UploadedFile $uploadedFile, string $entityClass, string $type): array
    {
        $config = $this->uploadedFileConfig->getUploadedFileEntityConfigByClass($entityClass);

        return $this->uploadedFileRelationRepository->getEntityIdsForUploadedFile($uploadedFile, $config->getEntityName(), $type);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $file
     * @param string $entityClass
     * @param object[] $entities
     * @param string $type
     */
    protected function updateRelationsForUploadedFileByEntities(
        UploadedFile $file,
        string $entityClass,
        array $entities,
        string $type,
    ): void {
        $relationsEntityIds = [];

        if (count($entities) > 0) {
            foreach ($entities as $entity) {
                if (!($entity instanceof $entityClass)) {
                    throw new InvalidArgumentException(sprintf('All object in argument $entities must be of the same class, got %s and %s', $entityClass, get_class($entity)));
                }

                if (!in_array($entity->getId(), $relationsEntityIds, true)) {
                    $relationsEntityIds[] = $entity->getId();
                }
            }
        }

        $uploadedFileEntityConfig = $this->uploadedFileConfig->getUploadedFileEntityConfigByClass($entityClass);
        $entityName = $uploadedFileEntityConfig->getEntityName();

        $currentRelationsEntityIds = $this->uploadedFileRelationRepository->getEntityIdsForUploadedFile($file, $entityName, $type);

        $idsToAdd = array_diff($relationsEntityIds, $currentRelationsEntityIds);
        $idsToRemove = array_diff($currentRelationsEntityIds, $relationsEntityIds);

        $positions = array_fill_keys($idsToAdd, -1);
        $positions = array_replace(
            $positions,
            $this->uploadedFileRelationRepository->maxPositionsByEntityNameAndIds($entityName, $idsToAdd, $type),
        );

        foreach ($idsToAdd as $id) {
            $this->createRelation($entityName, $id, $file, ++$positions[$id], $type);
        }

        $this->uploadedFileRelationRepository->deleteRelationsByEntityNameAndIdsAndUploadedFiles(
            $entityName,
            $idsToRemove,
            [$file],
            $type,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData $uploadedFileData
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelation[] $currentRelations
     * @param string $entityName
     * @param object $entity
     * @param int $startPosition
     * @param string $type
     */
    protected function createRelations(
        UploadedFileData $uploadedFileData,
        array $currentRelations,
        string $entityName,
        object $entity,
        int $startPosition,
        string $type,
    ): void {
        $relations = $uploadedFileData->relations;

        $currentRelationsIds = array_map(
            fn (UploadedFileRelation $relation) => $relation->getUploadedFile()->getId(),
            $currentRelations,
        );

        foreach ($relations as $key => $uploadedFile) {
            $relationFilename = $uploadedFileData->relationsFilenames[$key] ?? null;

            if ($relationFilename) {
                $uploadedFile->setNameAndSlug($relationFilename);
            }

            $uploadedFile->setTranslatedNames($uploadedFileData->relationsNames[$key] ?? []);

            if (in_array($uploadedFile->getId(), $currentRelationsIds, true)) {
                continue;
            }

            $this->createRelation(
                $entityName,
                $entity->getId(),
                $uploadedFile,
                $startPosition++,
                $type,
            );
        }
    }

    /**
     * @param int $uploadedFileId
     * @return array<string, string>
     */
    public function getTranslationsIndexedByLocaleForUploadedFileId(int $uploadedFileId): array
    {
        $translations = $this->uploadedFileRepository->getAllTranslationsByUploadedFileId($uploadedFileId);

        $translationsByLocale = [];

        foreach ($translations as $translation) {
            $translationsByLocale[$translation->getLocale()] = $translation->getName();
        }

        return $translationsByLocale;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileRepositoryInterface
     */
    protected function getRepository(): UploadedFileRepositoryInterface
    {
        return $this->uploadedFileRepository;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileLocator
     */
    protected function getFileLocator(): AbstractUploadedFileLocator
    {
        return $this->uploadedFileLocator;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfigInterface
     */
    protected function getUploadedFileConfig(): UploadedFileConfigInterface
    {
        return $this->uploadedFileConfig;
    }
}
