<?php

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class ImageUploadDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(
        protected readonly ImageFacade $imageFacade
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    protected function createInstance(): ImageUploadData
    {
        return new ImageUploadData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public function create(): ImageUploadData
    {
        return $this->createInstance();
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public function createFromEntityAndType(object $entity, ?string $type = null): ImageUploadData
    {
        $imageUploadData = $this->createInstance();
        $this->fillFromEntityAndType($imageUploadData, $entity, $type);

        return $imageUploadData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData $imageUploadData
     * @param object $entity
     * @param string|null $type
     */
    protected function fillFromEntityAndType(ImageUploadData $imageUploadData, object $entity, ?string $type): void
    {
        $orderedImages = $this->imageFacade->getImagesByEntityIndexedById($entity, $type);
        $imageUploadData->orderedImages = $orderedImages;

        foreach ($orderedImages as $orderedImage) {
            $imageUploadData->namesIndexedByImageIdAndLocale[$orderedImage->getId()] = $orderedImage->getNames();
        }
    }
}
