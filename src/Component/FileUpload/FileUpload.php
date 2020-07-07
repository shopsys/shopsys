<?php

declare(strict_types=1);

namespace App\Component\FileUpload;

use League\Flysystem\FileNotFoundException;
use Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload as BaseFileUpload;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUpload extends BaseFileUpload
{
    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return string
     */
    public function upload(UploadedFile $file)
    {
        if ($file->getError()) {
            throw new \Shopsys\FrameworkBundle\Component\FileUpload\Exception\UploadFailedException($file->getErrorMessage());
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
            /* @var $fileForUpload FileForUpload */
            $sourceFilepath = TransformString::removeDriveLetterFromPath($this->getTemporaryFilepath($fileForUpload->getTemporaryFilename()));
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
            } catch (\Symfony\Component\Filesystem\Exception\IOException $ex) {
                $message = 'Failed to rename file from temporary directory to entity';
                throw new \Shopsys\FrameworkBundle\Component\FileUpload\Exception\MoveToEntityFailedException($message, $ex);
            }
        }
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function tryDeleteTemporaryFile($filename)
    {
        if (!empty($filename)) {
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
     * @param int $howOldInSeconds
     * @return int Count of deleted files
     */
    public function deleteOldUploadedFiles(int $howOldInSeconds): int
    {
        $deletedCounter = 0;
        $currentTimestamp = time();

        $uploadedFiles = $this->filesystem->listContents($this->getTemporaryDirectory());
        foreach ($uploadedFiles as $uploadedFile) {
            if ($uploadedFile['type'] === 'file' && $currentTimestamp - $uploadedFile['timestamp'] > $howOldInSeconds) {
                $this->filesystem->delete($uploadedFile['path']);
                $deletedCounter++;
            }
        }

        return $deletedCounter;
    }
}
