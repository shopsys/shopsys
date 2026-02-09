<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class ImageUploadDataFactory
{
    public function __construct(
        protected readonly ImageFacade $imageFacade,
    ) {
    }

    protected function createInstance(): ImageUploadData
    {
        return new ImageUploadData();
    }

    public function create(): ImageUploadData
    {
        return $this->createInstance();
    }

    public function createFromEntityAndType(object $entity, ?string $type = null): ImageUploadData
    {
        $imageUploadData = $this->createInstance();
        $this->fillFromEntityAndType($imageUploadData, $entity, $type);

        return $imageUploadData;
    }

    protected function fillFromEntityAndType(ImageUploadData $imageUploadData, object $entity, ?string $type): void
    {
        $orderedImages = $this->imageFacade->getImagesByEntityIndexedById($entity, $type);
        $imageUploadData->orderedImages = $orderedImages;

        foreach ($orderedImages as $orderedImage) {
            $imageUploadData->namesIndexedByImageIdAndLocale[$orderedImage->getId()] = $orderedImage->getNames();
        }
    }
}
