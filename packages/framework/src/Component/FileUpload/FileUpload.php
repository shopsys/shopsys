<?php

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUpload
{
    const TEMPORARY_DIRECTORY = 'fileUploads';

    /**
     * @var string
     */
    private $temporaryDir;

    /**
     * @var string
     */
    private $uploadedFileDir;

    /**
     * @var string
     */
    private $imageDir;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention
     */
    private $fileNamingConvention;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $localFilesystem;

    /**
     * @var \League\Flysystem\MountManager
     */
    private $mountManager;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;
    
    public function __construct(
        string $temporaryDir,
        string $uploadedFileDir,
        string $imageDir,
        FileNamingConvention $fileNamingConvention,
        Filesystem $symfonyFilesystem,
        MountManager $mountManager,
        FilesystemInterface $filesystem
    ) {
        $this->temporaryDir = $temporaryDir;
        $this->uploadedFileDir = $uploadedFileDir;
        $this->imageDir = $imageDir;
        $this->fileNamingConvention = $fileNamingConvention;
        $this->localFilesystem = $symfonyFilesystem;
        $this->mountManager = $mountManager;
        $this->filesystem = $filesystem;
    }

    public function upload(UploadedFile $file): string
    {
        if ($file->getError()) {
            throw new \Shopsys\FrameworkBundle\Component\FileUpload\Exception\UploadFailedException($file->getErrorMessage(), $file->getError());
        }

        $temporaryFilename = $this->getTemporaryFilename($file->getClientOriginalName());
        $file->move($this->getTemporaryDirectory(), $temporaryFilename);

        return $temporaryFilename;
    }
    
    public function tryDeleteTemporaryFile(string $filename): bool
    {
        if (!empty($filename)) {
            $filepath = $this->getTemporaryFilepath($filename);
            try {
                $this->localFilesystem->remove($filepath);
            } catch (\Symfony\Component\Filesystem\Exception\IOException $ex) {
                return false;
            }
        }
        return true;
    }
    
    private function getTemporaryFilename(string $filename): string
    {
        return TransformString::safeFilename(uniqid('', true) . '__' . $filename);
    }
    
    public function getTemporaryFilepath(string $temporaryFilename): string
    {
        return $this->getTemporaryDirectory() . '/' . TransformString::safeFilename($temporaryFilename);
    }

    public function getTemporaryDirectory(): string
    {
        return $this->temporaryDir . '/' . self::TEMPORARY_DIRECTORY;
    }

    public function getUploadDirectory(string $isImage, string $category, ?string $targetDirectory): string
    {
        return ($isImage ? $this->imageDir : $this->uploadedFileDir)
            . $category
            . ($targetDirectory !== null ? '/' . $targetDirectory : '');
    }

    private function getTargetFilepath(string $filename, bool $isImage, string $category, ?string $targetDirectory): string
    {
        return $this->getUploadDirectory($isImage, $category, $targetDirectory) . '/' . $filename;
    }
    
    public function getOriginalFilenameByTemporary(string $temporaryFilename): string
    {
        $matches = [];
        if ($temporaryFilename && preg_match('/^.+?__(.+)$/', $temporaryFilename, $matches)) {
            return $matches[1];
        }
        return $temporaryFilename;
    }

    public function preFlushEntity(EntityFileUploadInterface $entity): void
    {
        $filesForUpload = $entity->getTemporaryFilesForUpload();
        foreach ($filesForUpload as $key => $fileForUpload) {
            $originalFilename = $this->getOriginalFilenameByTemporary($fileForUpload->getTemporaryFilename());
            $entity->setFileAsUploaded($key, $originalFilename);
        }
    }

    public function postFlushEntity(EntityFileUploadInterface $entity): void
    {
        $filesForUpload = $entity->getTemporaryFilesForUpload();
        foreach ($filesForUpload as $fileForUpload) {
            /* @var $fileForUpload FileForUpload */
            $sourceFilepath = $this->getTemporaryFilepath($fileForUpload->getTemporaryFilename());
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

                $this->mountManager->move('local://' . $sourceFilepath, 'main://' . $targetFilename);
            } catch (\Symfony\Component\Filesystem\Exception\IOException $ex) {
                $message = 'Failed to rename file from temporary directory to entity';
                throw new \Shopsys\FrameworkBundle\Component\FileUpload\Exception\MoveToEntityFailedException($message, $ex);
            }
        }
    }
}
