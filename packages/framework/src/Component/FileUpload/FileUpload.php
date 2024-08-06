<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use League\Flysystem\StorageAttributes;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\MissingFileClassDirectoryMappingException;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\MoveToEntityFailedException;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\UploadFailedException;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUpload
{
    protected const TEMPORARY_DIRECTORY = 'fileUploads';
    protected const DELETE_OLD_FILES_SECONDS = 86400;

    protected string $temporaryDir;

    /**
     * @param string $temporaryDir
     * @param array<string, string> $directoriesByFileClass
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention $fileNamingConvention
     * @param \League\Flysystem\MountManager $mountManager
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     */
    public function __construct(
        $temporaryDir,
        protected array $directoriesByFileClass,
        protected readonly FileNamingConvention $fileNamingConvention,
        protected readonly MountManager $mountManager,
        protected readonly FilesystemOperator $filesystem,
        protected readonly ParameterBagInterface $parameterBag,
    ) {
        $this->temporaryDir = $temporaryDir;
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
