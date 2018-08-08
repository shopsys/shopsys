<?php

namespace Shopsys\FrameworkBundle\Component\Image;

use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessingService;

class ImageService
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessingService
     */
    private $imageProcessingService;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload
     */
    private $fileUpload;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface
     */
    protected $imageFactory;

    public function __construct(
        ImageProcessingService $imageProcessingService,
        FileUpload $fileUpload,
        ImageFactoryInterface $imageFactory
    ) {
        $this->imageProcessingService = $imageProcessingService;
        $this->fileUpload = $fileUpload;
        $this->imageFactory = $imageFactory;
    }

    /**
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getUploadedImages(ImageEntityConfig $imageEntityConfig, int $entityId, array $temporaryFilenames, ?string $type): array
    {
        if (!$imageEntityConfig->isMultiple($type)) {
            $message = 'Entity ' . $imageEntityConfig->getEntityClass()
                . ' is not allowed to have multiple images for type ' . ($type ?: 'NULL');
            throw new \Shopsys\FrameworkBundle\Component\Image\Exception\EntityMultipleImageException($message);
        }

        $images = [];
        foreach ($temporaryFilenames as $temporaryFilename) {
            $images[] = $this->createImage($imageEntityConfig, $entityId, $temporaryFilename, $type);
        }

        return $images;
    }

    /**
     * @param string|null $type
     */
    public function createImage(
        ImageEntityConfig $imageEntityConfig,
        int $entityId,
        string $temporaryFilename,
        ?string $type
    ): \Shopsys\FrameworkBundle\Component\Image\Image {
        $temporaryFilepath = $this->fileUpload->getTemporaryFilepath($temporaryFilename);

        $image = $this->imageFactory->create(
            $imageEntityConfig->getEntityName(),
            $entityId,
            $type,
            $this->imageProcessingService->convertToShopFormatAndGetNewFilename($temporaryFilepath)
        );

        return $image;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $images
     */
    public function deleteImages(string $entityName, int $entityId, array $images): void
    {
        foreach ($images as $image) {
            $this->deleteImage($entityName, $entityId, $image);
        }
    }
    
    private function deleteImage(string $entityName, int $entityId, Image $image): void
    {
        if ($image->getEntityName() !== $entityName
            || $image->getEntityId() !== $entityId
        ) {
            throw new \Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException(
                sprintf(
                    'Entity %s with ID %s does not own image with ID %s',
                    $entityName,
                    $entityId,
                    $image->getId()
                )
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $orderedImages
     */
    public function setImagePositionsByOrder($orderedImages): void
    {
        $position = 0;
        foreach ($orderedImages as $image) {
            $image->setPosition($position);
            $position++;
        }
    }
}
