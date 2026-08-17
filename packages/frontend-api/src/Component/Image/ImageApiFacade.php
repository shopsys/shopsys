<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Image;

class ImageApiFacade
{
    public function __construct(
        protected readonly ImageApiRepository $imageApiRepository,
    ) {
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
