<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessingService;
use Symfony\Bridge\Monolog\Logger;

class DomainService
{
    const DOMAIN_ICON_WIDTH = 48;
    const DOMAIN_ICON_HEIGHT = 33;
    const DOMAIN_ICON_CROP = false;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessingService
     */
    private $imageProcessingService;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    public function __construct(
        Logger $logger,
        ImageProcessingService $imageProcessingService,
        FilesystemInterface $filesystem
    ) {
        $this->logger = $logger;
        $this->imageProcessingService = $imageProcessingService;
        $this->filesystem = $filesystem;
    }

    public function convertToDomainIconFormatAndSave(int $domainId, string $filepath, string $domainImagesDirectory): void
    {
        $resizedImage = $this->imageProcessingService->resize(
            $this->imageProcessingService->createInterventionImage($filepath),
            self::DOMAIN_ICON_WIDTH,
            self::DOMAIN_ICON_HEIGHT,
            self::DOMAIN_ICON_CROP
        );
        $resizedImage->encode(ImageProcessingService::EXTENSION_PNG);

        $targetFilePath = $domainImagesDirectory . '/' . $domainId . '.' . ImageProcessingService::EXTENSION_PNG;

        try {
            $this->filesystem->put($targetFilePath, $resizedImage);
        } catch (\Exception $ex) {
            $message = 'Move file from temporary directory to domain directory failed';
            $moveToFolderFailedException = new \Shopsys\FrameworkBundle\Component\FileUpload\Exception\MoveToFolderFailedException(
                $message,
                $ex
            );
            $this->logger->addError($message, ['exception' => $moveToFolderFailedException]);
            throw $moveToFolderFailedException;
        }
    }
}
