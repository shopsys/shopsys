<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Image;

use Shopsys\FrameworkBundle\Component\Image\ImageRepository;

class ImageApiFacade
{
    public function __construct(
        protected readonly ImageRepository $imageRepository,
        protected readonly ImageApiRepository $imageApiRepository,
    ) {
    }

    /**
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
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[][]
     */
    public function getAllImagesIndexedByEntityId(array $entityIds, string $entityName, ?string $type): array
    {
        return $this->imageApiRepository->getAllImagesIndexedByEntityId($entityIds, $entityName, $type);
    }

    /**
     * @param int[] $entityIds
     * @return array<int, int>
     */
    public function getImageCountsIndexedByEntityId(array $entityIds, string $entityName, ?string $type): array
    {
        return $this->imageApiRepository->getImageCountsIndexedByEntityId($entityIds, $entityName, $type);
    }
}
