<?php

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
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

    /**
     * @var string
     */
    protected $temporaryDir;

    /**
     * @var string
     */
    protected $uploadedFileDir;

    /**
     * @var string
     */
    protected $imageDir;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention
     */
    protected $fileNamingConvention;

    /**
     * @var \League\Flysystem\MountManager
     */
    protected $mountManager;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    protected $parameterBag;

    /**
     * @param string $temporaryDir
     * @param string $uploadedFileDir
     * @param string $imageDir
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention $fileNamingConvention
     * @param \League\Flysystem\MountManager $mountManager
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     */
    public function __construct(
        $temporaryDir,
        $uploadedFileDir,
        $imageDir,
        FileNamingConvention $fileNamingConvention,
        MountManager $mountManager,
        FilesystemInterface $filesystem,
        ParameterBagInterface $parameterBag
    ) {
        $this->temporaryDir = $temporaryDir;
        $this->uploadedFileDir = $uploadedFileDir;
        $this->imageDir = $imageDir;
        $this->fileNamingConvention = $fileNamingConvention;
        $this->mountManager = $mountManager;
        $this->filesystem = $filesystem;
        $this->parameterBag = $parameterBag;
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
            'main://' . $this->getTemporaryDirectory() . '/' . $temporaryFilename
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
            } catch (FileNotFoundException $ex) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function getTemporaryFilename($filename)
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
        return $this->parameterBag->get(
            'kernel.root_dir'
        ) . $this->getTemporaryDirectory() . '/' . TransformString::safeFilename(
            $temporaryFilename
        );
    }

    /**
     * @return string
     */
    public function getTemporaryDirectory()
    {
        return $this->temporaryDir . '/' . static::TEMPORARY_DIRECTORY;
    }

    /**
     * @param bool $isImage
     * @param string $category
     * @param string|null $targetDirectory
     * @return string
     */
    public function getUploadDirectory($isImage, $category, $targetDirectory)
    {
        return ($isImage ? $this->imageDir : $this->uploadedFileDir)
            . $category
            . ($targetDirectory !== null ? '/' . $targetDirectory : '');
    }

    /**
     * @param string $filename
     * @param bool $isImage
     * @param string $category
     * @param string|null $targetDirectory
     * @return string
     */
    protected function getTargetFilepath($filename, $isImage, $category, $targetDirectory)
    {
        return $this->getUploadDirectory($isImage, $category, $targetDirectory) . '/' . $filename;
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

        /** @var \Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload $fileForUpload */
        foreach ($filesForUpload as $fileForUpload) {
            $sourceFilepath = TransformString::removeDriveLetterFromPath(
                $this->getTemporaryFilepath($fileForUpload->getTemporaryFilename())
            );
            $originalFilename = $this->fileNamingConvention->getFilenameByNamingConvention(
                $fileForUpload->getNameConventionType(),
                $fileForUpload->getTemporaryFilename(),
                $entity->getId()
            );
            $targetFilename = $this->getTargetFilepath(
                $originalFilename,
                $fileForUpload->isImage(),
                $fileForUpload->getCategory(),
                $fileForUpload->getTargetDirectory()
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
     * @return int Count of deleted files
     */
    public function deleteOldUploadedFiles(): int
    {
        $deletedCounter = 0;
        $currentTimestamp = time();
        $uploadedFiles = $this->filesystem->listContents($this->getTemporaryDirectory());

        foreach ($uploadedFiles as $uploadedFile) {
            if (
                $uploadedFile['type'] === 'file'
                && $currentTimestamp - $uploadedFile['timestamp'] >= static::DELETE_OLD_FILES_SECONDS
            ) {
                $this->filesystem->delete($uploadedFile['path']);
                $deletedCounter++;
            }
        }

        return $deletedCounter;
    }
}
