<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use InvalidArgumentException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use League\Flysystem\StorageAttributes;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileRepository;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\MissingFileClassDirectoryMappingException;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\MoveToEntityFailedException;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\UploadFailedException;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile as ShopsysUploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUpload
{
    protected const string TEMPORARY_DIRECTORY = 'fileUploads';
    protected const int DELETE_OLD_FILES_SECONDS = 86400;
    protected const string POSITION_BY_ENTITY_AND_TYPE_CACHE_NAMESPACE = 'positionByEntityAndType';

    /**
     * @param string $temporaryDir
     * @param array $directoriesByFileClass
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention $fileNamingConvention
     * @param \League\Flysystem\MountManager $mountManager
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageRepository $imageRepository
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileRepository $customerUploadedFileRepository
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     * @param \Shopsys\FrameworkBundle\Component\String\TransformStringHelper $transformStringHelper
     */
    public function __construct(
        protected readonly string $temporaryDir,
        protected array $directoriesByFileClass,
        protected readonly FileNamingConvention $fileNamingConvention,
        protected readonly MountManager $mountManager,
        protected readonly FilesystemOperator $filesystem,
        protected readonly ParameterBagInterface $parameterBag,
        protected readonly ImageRepository $imageRepository,
        protected readonly CustomerUploadedFileRepository $customerUploadedFileRepository,
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return string
     */
    public function upload(UploadedFile $file): string
    {
        if ($file->getError()) {
            throw new UploadFailedException($file->getErrorMessage());
        }

        $temporaryFilename = $this->getTemporaryFilename($file->getClientOriginalName());
        $this->mountManager->move(
            'local://' . $file->getRealPath(),
            'main://' . $this->getTemporaryDirectory() . '/' . $temporaryFilename,
        );

        return $temporaryFilename;
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function tryDeleteTemporaryFile(string $filename): bool
    {
        if ($filename !== '') {
            $filepath = $this->getTemporaryFilepath($filename);

            try {
                $this->filesystem->delete($filepath);
            } catch (FilesystemException) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getTemporaryFilename(string $filename): string
    {
        return $this->transformStringHelper->safeFilename(uniqid('', true) . '__' . $filename);
    }

    /**
     * @param string $temporaryFilename
     * @return string
     */
    public function getTemporaryFilepath(string $temporaryFilename): string
    {
        return $this->getTemporaryDirectory() . '/' . $this->transformStringHelper->safeFilename($temporaryFilename);
    }

    /**
     * @param string $temporaryFilename
     * @return string
     */
    public function getAbsoluteTemporaryFilepath(string $temporaryFilename): string
    {
        return $this->parameterBag->get('kernel.project_dir') . $this->getTemporaryDirectory() . '/' . $this->transformStringHelper->safeFilename($temporaryFilename);
    }

    /**
     * @return string
     */
    public function getTemporaryDirectory(): string
    {
        return $this->temporaryDir . '/' . static::TEMPORARY_DIRECTORY;
    }

    /**
     * @param string $fileClass
     * @param string $category
     * @param string|null $targetDirectory
     * @return string
     */
    public function getUploadDirectory(string $fileClass, string $category, ?string $targetDirectory): string
    {
        return $this->getDirectoryByFileClass($fileClass)
            . $category
            . ($targetDirectory !== null ? '/' . $targetDirectory : '');
    }

    /**
     * @param string $filename
     * @param string $fileClass
     * @param string $category
     * @param string|null $targetDirectory
     * @return string
     */
    protected function getTargetFilepath(
        string $filename,
        string $fileClass,
        string $category,
        ?string $targetDirectory,
    ): string {
        return $this->getUploadDirectory($fileClass, $category, $targetDirectory) . '/' . $filename;
    }

    /**
     * @param string $temporaryFilename
     * @return string
     */
    public function getOriginalFilenameByTemporary(string $temporaryFilename): string
    {
        $matches = [];

        if ($temporaryFilename && preg_match('/^.+?__(.+)$/', $temporaryFilename, $matches)) {
            return $matches[1];
        }

        return $temporaryFilename;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface $entity
     */
    public function preFlushEntity(EntityFileUploadInterface $entity): void
    {
        $filesForUpload = $entity->getTemporaryFilesForUpload();

        foreach ($filesForUpload as $key => $fileForUpload) {
            $originalFilename = $this->getOriginalFilenameByTemporary($fileForUpload->getTemporaryFilename());
            $entity->setFileAsUploaded($key, $originalFilename);
        }

        if (($entity instanceof Image || $entity instanceof CustomerUploadedFile) && $entity->getId() === null) {
            $entity->setPosition($this->getPositionForNewEntity($entity));
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface $entity
     */
    public function postFlushEntity(EntityFileUploadInterface $entity): void
    {
        $filesForUpload = $entity->getTemporaryFilesForUpload();

        foreach ($filesForUpload as $key => $fileForUpload) {
            $sourceFilepath = $this->transformStringHelper->removeDriveLetterFromPath(
                $this->getTemporaryFilepath($fileForUpload->getTemporaryFilename()),
            );
            $originalFilename = $this->fileNamingConvention->getFilenameByNamingConvention(
                $fileForUpload->getNameConventionType(),
                $fileForUpload->getTemporaryFilename(),
                $entity->getId(),
            );
            $targetFilename = $this->getTargetFilepath(
                $originalFilename,
                $fileForUpload->getFileClass(),
                $fileForUpload->getCategory(),
                $fileForUpload->getTargetDirectory(),
            );

            try {
                if ($this->filesystem->has($targetFilename)) {
                    $this->filesystem->delete($targetFilename);
                }

                $this->mountManager->move('main://' . $sourceFilepath, 'main://' . $targetFilename);
                $entity->setFileKeyAsUploaded($key);
            } catch (IOException $ex) {
                $message = 'Failed to rename file from temporary directory to entity';

                throw new MoveToEntityFailedException($message, $ex);
            }
        }
    }

    /**
     * @param \League\Flysystem\StorageAttributes $uploadedFile
     * @param int $currentTimestamp
     * @return bool
     */
    protected function shouldDeleteFile(StorageAttributes $uploadedFile, int $currentTimestamp): bool
    {
        return $uploadedFile->isFile() && $currentTimestamp - $uploadedFile->lastModified() >= static::DELETE_OLD_FILES_SECONDS;
    }

    /**
     * @return int Count of deleted files
     */
    public function deleteOldUploadedFiles(): int
    {
        $deletedCounter = 0;
        $currentTimestamp = time();
        $uploadedFiles = $this->filesystem->listContents($this->getTemporaryDirectory());

        foreach ($uploadedFiles as $uploadedFile) {
            if ($this->shouldDeleteFile($uploadedFile, $currentTimestamp)) {
                $this->filesystem->delete($uploadedFile->path());
                $deletedCounter++;
            }
        }

        return $deletedCounter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $entity
     * @return int
     */
    protected function getPositionForNewEntity(EntityFileUploadInterface $entity): int
    {
        $entityName = $entity->getEntityName();
        $entityId = $entity->getEntityId();
        $type = $entity->getType();
        $uploadEntityType = $this->getUploadEntityType($entity);
        $key = $this->inMemoryCache->getKey($entityName, $entityId, $type, $uploadEntityType);

        if ($this->inMemoryCache->hasItem(self::POSITION_BY_ENTITY_AND_TYPE_CACHE_NAMESPACE, $key)) {
            $position = $this->inMemoryCache->getItem(self::POSITION_BY_ENTITY_AND_TYPE_CACHE_NAMESPACE, $key);
            $position++;

            $this->inMemoryCache->save(self::POSITION_BY_ENTITY_AND_TYPE_CACHE_NAMESPACE, $position, $key);

            return $position;
        }

        $position = match ($uploadEntityType) {
            'image' => $this->imageRepository->getNewImagePosition(
                $entityName,
                $entityId,
                $type,
            ),
            'customerFile' => $this->customerUploadedFileRepository->getNewCustomerUploadedFilePosition(
                $entityName,
                $entityId,
                $type,
            ),
            default => throw new InvalidArgumentException(
                sprintf('Unknown upload entity type "%s"', $uploadEntityType),
            ),
        };

        $this->inMemoryCache->save(self::POSITION_BY_ENTITY_AND_TYPE_CACHE_NAMESPACE, $position, $key);

        return $position;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface $entity
     * @return string
     */
    protected function getUploadEntityType(EntityFileUploadInterface $entity): string
    {
        if ($entity instanceof Image) {
            $uploadEntityType = 'image';
        } elseif ($entity instanceof ShopsysUploadedFile) {
            $uploadEntityType = 'file';
        } elseif ($entity instanceof CustomerUploadedFile) {
            $uploadEntityType = 'customerFile';
        } else {
            throw new UnexpectedTypeException(
                sprintf('Provided entity with class %s was not expected.', $entity::class),
            );
        }

        return $uploadEntityType;
    }

    /**
     * @param string $fileClass
     * @return string
     */
    protected function getDirectoryByFileClass(string $fileClass): string
    {
        if (array_key_exists($fileClass, $this->directoriesByFileClass)) {
            return $this->directoriesByFileClass[$fileClass];
        }

        foreach ($this->directoriesByFileClass as $class => $dir) {
            if (is_subclass_of($fileClass, $class)) {
                return $dir;
            }
        }

        throw new MissingFileClassDirectoryMappingException(
            sprintf('Missing directory mapping for file class "%s"', $fileClass),
        );
    }
}
