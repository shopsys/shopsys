<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Image;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;

class ImageApiFacade
{
    protected const string IMAGE_API_CACHE_NAMESPACE = 'imageApi';
    protected const string ENTITY_IDS_WITH_IMAGE_CACHE_KEY = 'entityIdsWithImage';

    public function __construct(
        protected readonly ImageApiRepository $imageApiRepository,
        protected readonly InMemoryCache $inMemoryCache,
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

    /**
     * @return int[]
     */
    public function getEntityIdsWithImageByEntityName(string $entityName): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::IMAGE_API_CACHE_NAMESPACE,
            fn () => $this->imageApiRepository->getEntityIdsWithImageByEntityName($entityName),
            static::ENTITY_IDS_WITH_IMAGE_CACHE_KEY,
            $entityName,
        );
    }
}
