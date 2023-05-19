<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Exception;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\MoveToFolderFailedException;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;
use Symfony\Bridge\Monolog\Logger;

class DomainIconResizer
{
    protected const DOMAIN_ICON_WIDTH = 46;
    protected const DOMAIN_ICON_HEIGHT = 26;
    protected const DOMAIN_ICON_CROP = false;

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
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
    public function convertToDomainIconFormatAndSave($domainId, $filepath, $domainImagesDirectory)
    {
        $resizedImage = $this->imageProcessor->resize(
            $this->imageProcessor->createInterventionImage($filepath),
            static::DOMAIN_ICON_WIDTH,
            static::DOMAIN_ICON_HEIGHT,
            static::DOMAIN_ICON_CROP,
        );
        $resizedImage->encode(ImageProcessor::EXTENSION_PNG);

        $targetFilePath = $domainImagesDirectory . '/' . $domainId . '.' . ImageProcessor::EXTENSION_PNG;

        try {
            $this->filesystem->delete($targetFilePath);
            $this->filesystem->write($targetFilePath, $resizedImage->getEncoded());
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
