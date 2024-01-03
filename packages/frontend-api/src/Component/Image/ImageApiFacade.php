<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Image;

use Shopsys\FrameworkBundle\Component\Image\ImageRepository;

class ImageApiFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageRepository $imageRepository
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageApiRepository $imageApiRepository
     */
    public function __construct(
        protected readonly ImageRepository $imageRepository,
        protected readonly ImageApiRepository $imageApiRepository,
    ) {
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImagesByEntityIdAndNameIndexedById(int $entityId, string $entityName, ?string $type): array
    {
        return $this->imageRepository->getImagesByEntityIndexedById(
            $entityName,
            $entityId,
            $type,
        );
    }

    /**
     * @param int[] $entityIds
     * @param string $entityName
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]|null[]
     */
    public function getImagesByTypeAndPositionIndexedByEntityId(
        array $entityIds,
        string $entityName,
        ?string $type,
    ): array {
        return $this->imageApiRepository->getImagesByTypeAndPositionIndexedByEntityId(
            $entityIds,
            $entityName,
            $type,
        );
    }

    /**
     * @param int[] $entityIds
     * @param string $entityName
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[][]
     */
    public function getAllImagesIndexedByEntityId(array $entityIds, string $entityName, ?string $type): array
    {
        return $this->imageApiRepository->getAllImagesIndexedByEntityId($entityIds, $entityName, $type);
    }
}
