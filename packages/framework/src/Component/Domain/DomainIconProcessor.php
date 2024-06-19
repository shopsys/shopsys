<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Exception;
use League\Flysystem\FilesystemOperator;
use Monolog\Logger;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\MoveToFolderFailedException;
use Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;

class DomainIconProcessor
{
    /**
     * @param \Monolog\Logger $logger
     * @param \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor $imageProcessor
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        protected readonly Logger $logger,
        protected readonly ImageProcessor $imageProcessor,
        protected readonly FilesystemOperator $filesystem,
    ) {
    }

    /**
     * @param int $domainId
     * @param string $filepath
     * @param string $domainImagesDirectory
     */
    public function saveIcon(
        int $domainId,
        string $filepath,
        string $domainImagesDirectory,
    ): void {
        $targetFilePath = $domainImagesDirectory . '/' . $domainId . '.' . ImageProcessor::EXTENSION_PNG;

        try {
            $mimeType = $this->filesystem->mimeType($filepath);

            if ($mimeType !== 'image/png') {
                throw new FileIsNotSupportedImageException('Only PNG images are supported');
            }
            $file = $this->filesystem->read($filepath);
            $this->filesystem->delete($targetFilePath);
            $this->filesystem->write($targetFilePath, $file);
        } catch (Exception $ex) {
            $message = 'Move file from temporary directory to domain directory failed';
            $moveToFolderFailedException = new MoveToFolderFailedException(
                $message,
                $ex,
            );
            $this->logger->error($message, ['exception' => $moveToFolderFailedException]);

            throw $moveToFolderFailedException;
        }
    }
}
