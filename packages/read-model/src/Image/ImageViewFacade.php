<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Image;

use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class ImageViewFacade implements ImageViewFacadeInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Image\ImageViewFactory
     */
    protected $imageViewFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFactory $imageViewFactory
     */
    public function __construct(ImageFacade $imageFacade, ImageViewFactory $imageViewFactory)
    {
        $this->imageFacade = $imageFacade;
        $this->imageViewFactory = $imageViewFactory;
    }

    /**
     * @param string $entityClass
     * @param int[] $entityIds
     * @return \Shopsys\ReadModelBundle\Image\ImageView[]|null[]
     */
    public function getMainImagesByEntityIds(string $entityClass, array $entityIds): array
    {
        $imagesIndexedByEntityIds = $this->imageFacade->getImagesByEntitiesIndexedByEntityId($entityIds, $entityClass);

        $imageViewsOrNullsIndexedByEntityIds = [];

        foreach ($entityIds as $entityId) {
            $imageOrNull = $this->getImageOrNullFromArray($imagesIndexedByEntityIds, $entityId);

            $imageViewsOrNullsIndexedByEntityIds[$entityId] = $this->createImageViewOrNullFromImage($imageOrNull);
        }

        return $imageViewsOrNullsIndexedByEntityIds;
    }

    /**
     * @param string $entityClass
     * @param int $entityId
     * @return \Shopsys\ReadModelBundle\Image\ImageView[]
     */
    public function getAllImagesByEntityId(string $entityClass, int $entityId): array
    {
        $images = $this->imageFacade->getImagesByEntityId($entityId, $entityClass);

        $imageViews = [];

        foreach ($images as $image) {
            $imageViews[] = $this->createImageViewOrNullFromImage($image);
        }

        return $imageViews;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|null $image
     * @return \Shopsys\ReadModelBundle\Image\ImageView|null
     */
    protected function createImageViewOrNullFromImage(?Image $image): ?ImageView
    {
        if ($image === null) {
            return null;
        }

        return $this->imageViewFactory->createFromImage($image);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $images
     * @param int $key
     * @return \Shopsys\FrameworkBundle\Component\Image\Image|null
     */
    protected function getImageOrNullFromArray(array $images, int $key): ?Image
    {
        if (!array_key_exists($key, $images)) {
            return null;
        }

        return $images[$key];
    }
}
