<?php

declare(strict_types=1);

namespace App\Component\FileUpload;

use App\Component\Image\Image;
use App\Component\Image\ImageRepository;
use App\Component\UploadedFile\UploadedFileRepository;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;
use Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\MoveToEntityFailedException;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\UploadFailedException;
use Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload as BaseFileUpload;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile as ShopsysUploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUpload extends BaseFileUpload
{
    /**
     * @var array<string, array<int, array<string, array<string|null, int>>>>
     */
    private array $positionByEntityAndType = [];

    /**
     * @param string $temporaryDir
     * @param string $uploadedFileDir
     * @param string $imageDir
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention $fileNamingConvention
     * @param \League\Flysystem\MountManager $mountManager
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     * @param \App\Component\Image\ImageRepository $imageRepository
     * @param \App\Component\UploadedFile\UploadedFileRepository $uploadedFileRepository
     */
    public function __construct(
        $temporaryDir,
        $uploadedFileDir,
        $imageDir,
        FileNamingConvention $fileNamingConvention,
        MountManager $mountManager,
        FilesystemOperator $filesystem,
        ParameterBagInterface $parameterBag,
        private readonly ImageRepository $imageRepository,
        private readonly UploadedFileRepository $uploadedFileRepository,
    ) {
        parent::__construct(
            $temporaryDir,
            $uploadedFileDir,
            $imageDir,
            $fileNamingConvention,
            $mountManager,
            $filesystem,
            $parameterBag,
        );
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
        $this->mountManager->move('local://' . $file->getRealPath(), 'main://' . $this->getTemporaryDirectory() . '/' . $temporaryFilename);

        return $temporaryFilename;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface $entity
     */
    public function postFlushEntity(EntityFileUploadInterface $entity)
    {
        $filesForUpload = $entity->getTemporaryFilesForUpload();

        foreach ($filesForUpload as $fileForUpload) {
            /** @var \Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload $fileForUpload */
            $sourceFilepath = TransformString::removeDriveLetterFromPath($this->getTemporaryFilepath($fileForUpload->getTemporaryFilename()));
            $originalFilename = $this->fileNamingConvention->getFilenameByNamingConvention(
                $fileForUpload->getNameConventionType(),
                $fileForUpload->getTemporaryFilename(),
                $entity->getId(),
            );
            $targetFilename = $this->getTargetFilepath(
                $originalFilename,
                $fileForUpload->isImage(),
                $fileForUpload->getCategory(),
                $fileForUpload->getTargetDirectory(),
            );

            try {
                if ($this->filesystem->has($targetFilename)) {
                    $this->filesystem->delete($targetFilename);
                }

                $this->mountManager->move('main://' . $sourceFilepath, 'main://' . $targetFilename);
            } catch (IOException $ex) {
                $message = 'Failed to rename file from temporary directory to entity';

                throw new MoveToEntityFailedException($message, $ex);
            }
        }
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function tryDeleteTemporaryFile($filename)
    {
        if ($filename !== null && $filename !== '') {
            $filepath = $this->getTemporaryFilepath($filename);

            try {
                $this->filesystem->delete($filepath);
            } catch (FilesystemException $ex) {
                return false;
            }
        }

        return true;
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
            if ($uploadedFile['type'] === 'file' && $currentTimestamp - $uploadedFile['timestamp'] > static::DELETE_OLD_FILES_SECONDS) {
                $this->filesystem->delete($uploadedFile['path']);
                $deletedCounter++;
            }
        }

        return $deletedCounter;
    }

    /**
     * @param \App\Component\Image\Image|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $entity
     */
    public function preFlushEntity(EntityFileUploadInterface $entity)
    {
        parent::preFlushEntity($entity);

        if ($entity->getPosition() === null) {
            $entity->setPosition($this->getPositionForNewEntity($entity));
        }
    }

    /**
     * @param \App\Component\Image\Image|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $entity
     * @return int
     */
    private function getPositionForNewEntity(EntityFileUploadInterface $entity): int
    {
        $entityName = $entity->getEntityName();
        $entityId = $entity->getEntityId();
        $type = $entity->getType();
        $uploadEntityType = $this->getUploadEntityType($entity);

        if (isset($this->positionByEntityAndType[$entityName][$entityId][$uploadEntityType][$type])) {
            $this->positionByEntityAndType[$entityName][$entityId][$uploadEntityType][$type]++;

            return $this->positionByEntityAndType[$entityName][$entityId][$uploadEntityType][$type];
        }

        if ($uploadEntityType === 'image') {
            $position = $this->imageRepository->getImagesCountByEntityIndexedById(
                $entityName,
                $entityId,
                $type,
            );
        } else {
            $position = $this->uploadedFileRepository->getUploadedFilesCountByEntityIndexedById(
                $entityName,
                $entityId,
                $type,
            );
        }

        $this->positionByEntityAndType[$entityName][$entityId][$uploadEntityType][$type] = $position;

        return $position;
    }

    /**
     * @param \App\Component\Image\Image|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $entity
     * @return string
     */
    private function getUploadEntityType(EntityFileUploadInterface $entity): string
    {
        $entityClass = get_class($entity);

        if ($entityClass === Image::class) {
            $uploadEntityType = 'image';
        } elseif ($entityClass === ShopsysUploadedFile::class) {
            $uploadEntityType = 'file';
        } else {
            throw new UnexpectedTypeException(
                sprintf('Provided entity with class %s was not expected.', $entityClass),
            );
        }

        return $uploadEntityType;
    }
}
