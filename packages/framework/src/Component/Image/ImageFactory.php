<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\EntityMultipleImageException;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;

class ImageFactory
{
    public function __construct(
        protected readonly ImageProcessor $imageProcessor,
        protected readonly FileUpload $fileUpload,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    protected function create(
        string $entityName,
        int $entityId,
        array $namesIndexedByLocale,
        string $temporaryFilename,
        ?string $type,
    ): Image {
        $temporaryFilePath = $this->fileUpload->getTemporaryFilepath($temporaryFilename);
        $convertedFilePath = $this->imageProcessor->convertToShopFormatAndGetNewFilename($temporaryFilePath);
        $filesize = $this->fileUpload->getTemporaryFilesize($convertedFilePath);

        $entityClassName = $this->entityNameResolver->resolve(Image::class);

        return new $entityClassName($entityName, $entityId, $namesIndexedByLocale, $convertedFilePath, $type, $filesize);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function createMultiple(
        ImageEntityConfig $imageEntityConfig,
        int $entityId,
        array $names,
        array $temporaryFilenames,
        ?string $type,
    ): array {
        if (!$imageEntityConfig->isMultiple($type) && count($temporaryFilenames) > 1) {
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
