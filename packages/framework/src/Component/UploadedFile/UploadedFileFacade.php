<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToRetrieveMetadata;
use Override;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileLocator;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileRepositoryInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
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
    protected const array VIEWABLE_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    public function __construct(
        FilesystemOperator $filesystem,
        EntityManagerInterface $em,
        TransformStringHelper $transformStringHelper,
        protected readonly UploadedFileConfig $uploadedFileConfig,
        protected readonly UploadedFileRepository $uploadedFileRepository,
        protected readonly UploadedFileLocator $uploadedFileLocator,
        protected readonly UploadedFileFactory $uploadedFileFactory,
        protected readonly UploadedFileRelationFactory $uploadedFileRelationFactory,
        protected readonly UploadedFileRelationRepository $uploadedFileRelationRepository,
    ) {
        parent::__construct($filesystem, $em, $transformStringHelper);
    }

    public function manageFiles(
        object $entity,
        UploadedFileData $uploadedFileData,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): void {
        $uploadedFileEntityConfig = $this->uploadedFileConfig->getUploadedFileEntityConfig($entity);
        $uploadedFileTypeConfig = $uploadedFileEntityConfig->getTypeByName($type);
        $entityName = $uploadedFileEntityConfig->getEntityName();

        $currentRelations = $this->uploadedFileRelationRepository->getByEntityNameAndIdAndUploadedFiles(
            $entityName,
            $this->getEntityId($entity),
            $uploadedFileData->orderedFiles,
            $type,
        );

        $this->updateFilesOrder($uploadedFileData->orderedFiles, $currentRelations);
        $this->updateFilenamesAndSlugs($uploadedFileData->currentFilenamesIndexedById);
        $this->updateTranslatedNames($uploadedFileData->namesIndexedById);

        $startPosition = $uploadedFileTypeConfig->isMultiple()
            ? $this->handleMultipleFiles($entity, $entityName, $type, $uploadedFileData)
            : $this->handleSingleFile($entity, $entityName, $type, $uploadedFileData);

        $this->createRelations(
            $uploadedFileData,
            $currentRelations,
            $entityName,
            $entity,
            $startPosition,
            $type,
            $uploadedFileTypeConfig->isMultiple(),
        );

        $this->deleteRelationsByEntityAndUploadedFiles($entity, $uploadedFileData->filesToDelete, $type);
    }

    /**
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

        $this->em->persist($newUploadedFile);
        $this->createRelation($entityName, $this->getEntityId($entity), $newUploadedFile, 0, $type);
    }

    /**
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
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function create(UploadedFileFormData $uploadedFileFormData): array
    {
        $uploadedFiles = $this->uploadFilesWithoutRelations(
            $uploadedFileFormData->files->uploadedFiles,
            $uploadedFileFormData->files->uploadedFilenames,
            $uploadedFileFormData->files->names,
        );

        $this->assignFilesToProducts($uploadedFiles, $uploadedFileFormData->products);

        return $uploadedFiles;
    }

    /**
     * @param array<int, array<string, string>> $namesIndexedByFileIdAndLocale
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    protected function uploadFilesWithoutRelations(
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
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $uploadedFiles
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    protected function assignFilesToProducts(
        array $uploadedFiles,
        array $products,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): void {
        if (count($uploadedFiles) === 0 || count($products) === 0) {
            return;
        }

        $uploadedFileEntityConfig = $this->uploadedFileConfig->getUploadedFileEntityConfigByClass(Product::class);
        $entityName = $uploadedFileEntityConfig->getEntityName();

        $productIds = array_map(static fn (Product $product) => $product->getId(), $products);
        $positions = $this->getMaxPositionsForEntities($entityName, $productIds, $type);

        foreach ($uploadedFiles as $file) {
            foreach ($products as $product) {
                $this->createRelation($entityName, $product->getId(), $file, ++$positions[$product->getId()], $type);
            }
        }
    }

    /**
     * @param int[] $entityIds
     * @return array<int, int>
     */
    protected function getMaxPositionsForEntities(string $entityName, array $entityIds, string $type): array
    {
        return array_replace(
            array_fill_keys($entityIds, -1),
            $this->uploadedFileRelationRepository->maxPositionsByEntityNameAndIds($entityName, $entityIds, $type),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $uploadedFiles
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
     * @return array<int, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile>
     */
    public function getByIdsIndexedById(array $uploadedFileIds): array
    {
        return $this->uploadedFileRepository->getByIdsIndexedById($uploadedFileIds);
    }

    public function getUploadedFileUrl(DomainConfig $domainConfig, UploadedFile $uploadedFile): string
    {
        return $this->uploadedFileLocator->getUploadedFileUrl($domainConfig, $uploadedFile);
    }

    public function getUploadedFileViewUrl(DomainConfig $domainConfig, UploadedFile $uploadedFile): string
    {
        return $this->uploadedFileLocator->getUploadedFileViewUrl($domainConfig, $uploadedFile);
    }

    public function getUploadedFileSize(UploadedFile $uploadedFile): ?int
    {
        try {
            return $this->filesystem->fileSize(
                $this->uploadedFileLocator->getAbsoluteUploadedFileFilepath($uploadedFile),
            );
        } catch (UnableToRetrieveMetadata) {
            return null;
        }
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
     * @param array<int, array<string, string>> $namesIndexedByFileIdAndLocale
     */
    protected function updateTranslatedNames(array $namesIndexedByFileIdAndLocale): void
    {
        foreach ($namesIndexedByFileIdAndLocale as $fileId => $names) {
            $this->getById($fileId)->setTranslatedNames($names);
        }
    }

    protected function handleMultipleFiles(
        object $entity,
        string $entityName,
        string $type,
        UploadedFileData $uploadedFileData,
    ): int {
        $this->uploadFiles(
            $entity,
            $entityName,
            $type,
            $uploadedFileData->uploadedFiles,
            $uploadedFileData->uploadedFilenames,
            count($uploadedFileData->orderedFiles),
            $uploadedFileData->names,
        );

        return count($uploadedFileData->orderedFiles) + count($uploadedFileData->uploadedFiles);
    }

    protected function handleSingleFile(
        object $entity,
        string $entityName,
        string $type,
        UploadedFileData $uploadedFileData,
    ): int {
        $temporaryFilename = array_last($uploadedFileData->uploadedFiles);
        $hasPickerSelection = count($uploadedFileData->relations) > 0;
        $orderedFiles = $uploadedFileData->orderedFiles;

        if (count($orderedFiles) > 1) {
            $filesToDelete = array_slice($orderedFiles, 1);
            $this->deleteRelationsByEntityAndUploadedFiles($entity, $filesToDelete, $type);
        }

        if (count($orderedFiles) > 0 && ($temporaryFilename || $hasPickerSelection)) {
            $this->deleteRelationsByEntityAndUploadedFiles($entity, [array_first($orderedFiles)], $type);
        }

        if ($temporaryFilename && !$hasPickerSelection) {
            $this->uploadFile(
                $entity,
                $entityName,
                $type,
                $temporaryFilename,
                array_last($uploadedFileData->uploadedFilenames) ?: '',
                array_last($uploadedFileData->names) ?: [],
            );

            return 1;
        }

        return 0;
    }

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

    protected function createRelation(
        string $entityName,
        int $entityId,
        UploadedFile $file,
        int $position,
        string $type,
    ): void {
        $relation = $this->uploadedFileRelationFactory->create($entityName, $entityId, $file, $position, $type);
        $this->em->persist($relation);
        $this->em->flush();
    }

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

        $filename = pathinfo($uploadedFileFormData->name, PATHINFO_FILENAME);

        $file->setName($filename);
        $file->setSlug($this->transformStringHelper->stringToFriendlyUrlSlug($filename));

        $this->updateRelationsForUploadedFileByEntities($file, Product::class, $uploadedFileFormData->products, UploadedFileTypeConfig::DEFAULT_TYPE_NAME);

        $this->em->persist($file);
        $this->em->flush();
    }

    public function deleteFile(UploadedFile $uploadedFile): void
    {
        $this->em->remove($uploadedFile);
        $this->em->flush();
    }

    /**
     * @return int[]
     */
    public function getEntityIdsForUploadedFile(UploadedFile $uploadedFile, string $entityClass, string $type): array
    {
        $config = $this->uploadedFileConfig->getUploadedFileEntityConfigByClass($entityClass);

        return $this->uploadedFileRelationRepository->getEntityIdsForUploadedFile($uploadedFile, $config->getEntityName(), $type);
    }

    /**
     * @param object[] $entities
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

        $positions = $this->getMaxPositionsForEntities($entityName, $idsToAdd, $type);

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
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelation[] $currentRelations
     */
    protected function createRelations(
        UploadedFileData $uploadedFileData,
        array $currentRelations,
        string $entityName,
        object $entity,
        int $startPosition,
        string $type,
        bool $isMultiple = true,
    ): void {
        $relations = $uploadedFileData->relations;

        if (!$isMultiple && count($relations) > 0) {
            if (count($currentRelations) > 0) {
                $existingFiles = array_map(
                    fn (UploadedFileRelation $r) => $r->getUploadedFile(),
                    $currentRelations,
                );
                $this->deleteRelationsByEntityAndUploadedFiles($entity, $existingFiles, $type);
            }
            $relations = [array_pop($relations)];
            $startPosition = 0;
            $currentRelations = [];
        }

        $currentRelationsIds = array_map(
            fn (UploadedFileRelation $relation) => $relation->getUploadedFile()->getId(),
            $currentRelations,
        );

        foreach ($relations as $key => $uploadedFile) {
            $relationFilename = $uploadedFileData->relationsFilenames[$key] ?? null;

            if ($relationFilename) {
                $filename = pathinfo($relationFilename, PATHINFO_FILENAME);

                $uploadedFile->setName($filename);
                $uploadedFile->setSlug($this->transformStringHelper->stringToFriendlyUrlSlug($filename));
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
     * @param int[] $uploadedFileIds
     * @return array<int, array<string, string>>
     */
    public function getTranslationsIndexedByLocaleForUploadedFileIds(array $uploadedFileIds): array
    {
        return $this->uploadedFileRepository->getTranslationsIndexedByFileIdAndLocale($uploadedFileIds);
    }

    #[Override]
    protected function getRepository(): UploadedFileRepositoryInterface
    {
        return $this->uploadedFileRepository;
    }

    #[Override]
    protected function getFileLocator(): AbstractUploadedFileLocator
    {
        return $this->uploadedFileLocator;
    }

    #[Override]
    protected function getUploadedFileConfig(): UploadedFileConfigInterface
    {
        return $this->uploadedFileConfig;
    }

    /**
     * @param int[] $entityIds
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[][]
     */
    public function getAllFilesIndexedByEntityId(
        array $entityIds,
        string $entityName,
        ?string $requiredLocale,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): array {
        return $this->uploadedFileRepository->getAllFilesIndexedByEntityId($entityIds, $entityName, $requiredLocale, $type);
    }

    public function isUploadedFileViewableInBrowser(UploadedFile $uploadedFile): bool
    {
        return in_array(strtolower($uploadedFile->getExtension()), static::VIEWABLE_EXTENSIONS, true);
    }
}
