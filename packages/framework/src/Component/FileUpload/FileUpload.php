<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use InvalidArgumentException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use League\Flysystem\StorageAttributes;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileRepository;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\MissingFileClassDirectoryMappingException;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\MoveToEntityFailedException;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\UploadFailedException;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile as ShopsysUploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Service\ResetInterface;

class FileUpload implements ResetInterface
{
    protected const TEMPORARY_DIRECTORY = 'fileUploads';
    protected const DELETE_OLD_FILES_SECONDS = 86400;

    /**
     * @var array<string, array<int, array<string, array<string|null, int>>>>
     */
    protected array $positionByEntityAndType = [];

    /**
     * @param string $temporaryDir
     * @param array<string, string> $directoriesByFileClass
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention $fileNamingConvention
     * @param \League\Flysystem\MountManager $mountManager
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageRepository $imageRepository
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileRepository $customerUploadedFileRepository
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
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return string
     */
    public function upload(UploadedFile $file)
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
    public function tryDeleteTemporaryFile($filename)
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
    public function getTemporaryFilename($filename)
    {
        return TransformString::safeFilename(uniqid('', true) . '__' . $filename);
    }

    /**
     * @param string $temporaryFilename
     * @return string
     */
    public function getTemporaryFilepath($temporaryFilename)
    {
        return $this->getTemporaryDirectory() . '/' . TransformString::safeFilename($temporaryFilename);
    }

    /**
     * @param string $temporaryFilename
     * @return string
     */
    public function getAbsoluteTemporaryFilepath($temporaryFilename)
    {
        return $this->parameterBag->get('kernel.project_dir') . $this->getTemporaryDirectory() . '/' . TransformString::safeFilename($temporaryFilename);
    }

    /**
     * @return string
     */
    public function getTemporaryDirectory()
    {
        return $this->temporaryDir . '/' . static::TEMPORARY_DIRECTORY;
    }

    /**
     * @param string $fileClass
     * @param string $category
     * @param string|null $targetDirectory
     * @return string
     */
    public function getUploadDirectory($fileClass, $category, $targetDirectory)
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
    protected function getTargetFilepath($filename, $fileClass, $category, $targetDirectory)
    {
        return $this->getUploadDirectory($fileClass, $category, $targetDirectory) . '/' . $filename;
    }

    /**
     * @param string $temporaryFilename
     * @return string
     */
    public function getOriginalFilenameByTemporary($temporaryFilename)
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
    public function preFlushEntity(EntityFileUploadInterface $entity)
    {
        $filesForUpload = $entity->getTemporaryFilesForUpload();

        foreach ($filesForUpload as $key => $fileForUpload) {
            $originalFilename = $this->getOriginalFilenameByTemporary($fileForUpload->getTemporaryFilename());
            $entity->setFileAsUploaded($key, $originalFilename);
        }

        if ($entity instanceof Image && $entity->getId() === null) {
            $entity->setPosition($this->getPositionForNewEntity($entity));
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface $entity
     */
    public function postFlushEntity(EntityFileUploadInterface $entity)
    {
        $filesForUpload = $entity->getTemporaryFilesForUpload();

        foreach ($filesForUpload as $key => $fileForUpload) {
            $sourceFilepath = TransformString::removeDriveLetterFromPath(
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

        if (isset($this->positionByEntityAndType[$entityName][$entityId][$uploadEntityType][$type])) {
            $this->positionByEntityAndType[$entityName][$entityId][$uploadEntityType][$type]++;

            return $this->positionByEntityAndType[$entityName][$entityId][$uploadEntityType][$type];
        }

        $position = match ($uploadEntityType) {
            'image' => $this->imageRepository->getImagesCountByEntityIndexedById(
                $entityName,
                $entityId,
                $type,
            ),
            'customerFile' => $this->customerUploadedFileRepository->getCustomerUploadedFilesCountByEntityIndexedById(
                $entityName,
                $entityId,
                $type,
            ),
            default => throw new InvalidArgumentException(
                sprintf('Unknown upload entity type "%s"', $uploadEntityType),
            ),
        };

        return $position;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $entity
     * @return string
     */
    protected function getUploadEntityType(EntityFileUploadInterface $entity): string
    {
        $entityClass = get_class($entity);

        if ($entityClass === Image::class) {
            $uploadEntityType = 'image';
        } elseif ($entityClass === ShopsysUploadedFile::class) {
            $uploadEntityType = 'file';
        } elseif ($entityClass === CustomerUploadedFile::class) {
            $uploadEntityType = 'customerFile';
        } else {
            throw new UnexpectedTypeException(
                sprintf('Provided entity with class %s was not expected.', $entityClass),
            );
        }

        return $uploadEntityType;
    }

    public function reset(): void
    {
        $this->positionByEntityAndType = [];
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
