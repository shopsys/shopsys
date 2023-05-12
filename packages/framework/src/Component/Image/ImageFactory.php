<?php

namespace Shopsys\FrameworkBundle\Component\Image;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\EntityMultipleImageException;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;

class ImageFactory implements ImageFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor $imageProcessor
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly ImageProcessor $imageProcessor,
        protected readonly FileUpload $fileUpload,
        protected readonly EntityNameResolver $entityNameResolver
    ) {
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param array $namesIndexedByLocale
     * @param string $temporaryFilename
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image
     */
    public function create(
        string $entityName,
        int $entityId,
        array $namesIndexedByLocale,
        string $temporaryFilename,
        ?string $type,
    ): Image {
        $temporaryFilePath = $this->fileUpload->getTemporaryFilepath($temporaryFilename);
        $convertedFilePath = $this->imageProcessor->convertToShopFormatAndGetNewFilename($temporaryFilePath);

        $classData = $this->entityNameResolver->resolve(Image::class);

        return new $classData($entityName, $entityId, $namesIndexedByLocale, $convertedFilePath, $type);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig $imageEntityConfig
     * @param int $entityId
     * @param array $names
     * @param array $temporaryFilenames
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function createMultiple(
        ImageEntityConfig $imageEntityConfig,
        int $entityId,
        array $names,
        array $temporaryFilenames,
        ?string $type,
    ): array {
        if (!$imageEntityConfig->isMultiple($type)) {
            $message = 'Entity ' . $imageEntityConfig->getEntityClass()
                . ' is not allowed to have multiple images for type ' . ($type ?: 'NULL');

            throw new EntityMultipleImageException($message);
        }

        $images = [];

        foreach ($temporaryFilenames as $key => $temporaryFilename) {
            $images[] = $this->create($imageEntityConfig->getEntityName(), $entityId, $names[$key] ?? [], $temporaryFilename, $type);
        }

        return $images;
    }
}
