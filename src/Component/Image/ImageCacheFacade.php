<?php

declare(strict_types=1);

namespace App\Component\Image;

use Doctrine\Common\Cache\CacheProvider;

class ImageCacheFacade
{
    private const IMAGE_CACHE_LIFETIME = 43200; // 12h

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $cacheProvider;

    /**
     * @param \Doctrine\Common\Cache\CacheProvider $cacheProvider
     */
    public function __construct(
        CacheProvider $cacheProvider
    ) {
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @return \App\Component\Image\Image|null
     */
    public function findCachedImageEntityByEntityNameAndEntityIdAndType(string $entityName, int $entityId, ?string $type): ?Image
    {
        $cacheId = $this->getCacheIdForSingleEntity($entityName, $entityId, $type);
        if ($this->cacheProvider->contains($cacheId)) {
            $image = $this->cacheProvider->fetch($cacheId);
            if ($image instanceof Image) {
                return $image;
            }
        }

        return null;
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @return \App\Component\Image\Image[]|null
     */
    public function findCachedImageEntitiesByEntityNameAndEntityIdAndType(string $entityName, int $entityId, ?string $type): ?array
    {
        $cacheId = $this->getCacheIdForMultipleEntities($entityName, $entityId, $type);
        if ($this->cacheProvider->contains($cacheId)) {
            $image = $this->cacheProvider->fetch($cacheId);
            if ($image !== false) {
                return $image;
            }
        }

        return null;
    }

    /**
     * @param int $imageId
     * @param string|null $type
     * @param string|null $sizeName
     * @param int|null $additionalIndex
     * @return string|null
     */
    public function findImageUrlInCache(int $imageId, ?string $type, ?string $sizeName, ?int $additionalIndex = null): ?string
    {
        $cacheId = $this->getCacheIdForImageUrl($imageId, $type, $sizeName, $additionalIndex);
        if ($this->cacheProvider->contains($cacheId)) {
            $url = $this->cacheProvider->fetch($cacheId);
            if ($url !== false) {
                return (string)$url;
            }
        }

        return null;
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @param \App\Component\Image\Image $image
     */
    public function setImageEntityIntoCacheByEntityNameAndEntityIdAndType(string $entityName, int $entityId, ?string $type, Image $image): void
    {
        $cacheId = $this->getCacheIdForSingleEntity($entityName, $entityId, $type);
        $this->cacheProvider->save($cacheId, $image, self::IMAGE_CACHE_LIFETIME);
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @param \App\Component\Image\Image[] $images
     */
    public function setImageEntitiesIntoCacheByEntityNameAndEntityIdAndType(string $entityName, int $entityId, ?string $type, array $images): void
    {
        $cacheId = $this->getCacheIdForMultipleEntities($entityName, $entityId, $type);
        $this->cacheProvider->save($cacheId, $images, self::IMAGE_CACHE_LIFETIME);
    }

    /**
     * @param string $url
     * @param int $imageId
     * @param string|null $type
     * @param string|null $sizeName
     * @param int|null $additionalIndex
     */
    public function setImageUrlIntoCache(string $url, int $imageId, ?string $type, ?string $sizeName, ?int $additionalIndex = null): void
    {
        $cacheId = $this->getCacheIdForImageUrl($imageId, $type, $sizeName, $additionalIndex);
        $this->cacheProvider->save($cacheId, $url, self::IMAGE_CACHE_LIFETIME);
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     */
    public function invalidateCacheByEntityNameAndEntityIdAndType(string $entityName, int $entityId, ?string $type): void
    {
        $cacheIdForSingleEntity = $this->getCacheIdForSingleEntity($entityName, $entityId, $type);
        $cacheIdForMultipleEntities = $this->getCacheIdForMultipleEntities($entityName, $entityId, $type);
        $this->cacheProvider->delete($cacheIdForSingleEntity);
        $this->cacheProvider->delete($cacheIdForMultipleEntities);
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @return string
     */
    private function getCacheIdForSingleEntity(string $entityName, int $entityId, ?string $type): string
    {
        if ($type === null) {
            return sprintf('cache_image:entity_%s_%d', $entityName, $entityId);
        }

        return sprintf('cache_image:entity_%s_%d_%s', $entityName, $entityId, $type);
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @return string
     */
    private function getCacheIdForMultipleEntities(string $entityName, int $entityId, ?string $type): string
    {
        if ($type === null) {
            return sprintf('cache_images:entities_%s_%d', $entityName, $entityId);
        }

        return sprintf('cache_images:entities_%s_%d_%s', $entityName, $entityId, $type);
    }

    /**
     * @param int $imageId
     * @param string|null $type
     * @param string|null $sizeName
     * @param int|null $additionalIndex
     * @return string
     */
    private function getCacheIdForImageUrl(int $imageId, ?string $type, ?string $sizeName, ?int $additionalIndex): string
    {
        return sprintf('ImageUrl:imageId-%d_type-%s_size-%s_additionalIndex-%d', $imageId, $type, $sizeName, $additionalIndex);
    }
}
