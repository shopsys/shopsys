<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\View;

use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

/**
 * @experimental
 */
class ImageViewFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(ImageFacade $imageFacade)
    {
        $this->imageFacade = $imageFacade;
    }

    /**
     * @param string $entityClass FQCN
     * @param int[] $entityIds
     * @return mixed
     */
    public function getImageViewsOrNullsIndexedByEntityIds(string $entityClass, array $entityIds): array
    {
        $imagesOrNullsIndexedByEntityIds = $this->imageFacade->getImagesOrNullsByEntitiesIndexedByEntityId($entityIds, $entityClass);

        $imageViewsOrNullsIndexedByEntityIds = [];
        foreach ($imagesOrNullsIndexedByEntityIds as $entityId => $imageOrNull) {
            if ($imageOrNull === null) {
                $imageViewsOrNullsIndexedByEntityIds[$entityId] = null;
            } else {
                $imageViewsOrNullsIndexedByEntityIds[$entityId] = new ImageView(
                    $imageOrNull->getId(),
                    $imageOrNull->getExtension(),
                    $imageOrNull->getEntityName(),
                    $imageOrNull->getType()
                );
            }
        }

        return $imageViewsOrNullsIndexedByEntityIds;
    }
}
