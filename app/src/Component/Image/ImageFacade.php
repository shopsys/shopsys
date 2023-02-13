<?php

declare(strict_types=1);

namespace App\Component\Image;

use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Psr\Log\LoggerInterface;
use Shopsys\Cdn\Component\Image\ImageFacade as CdnImageFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Image as BaseImage;
use Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * class don't inherit from \Shopsys\Cdn\Component\Image\ImageFacade and implement replacing manually until
 * https://gitlab.shopsys.cz/ss6-projects/cdn/-/merge_requests/3 is resolved
 *
 * @property \App\Component\Image\ImageRepository $imageRepository
 * @property \App\Component\FileUpload\FileUpload $fileUpload
 * @property \App\Component\Image\ImageLocator $imageLocator
 * @method \App\Component\Image\Image[] getImagesByEntityIdAndNameIndexedById(int $entityId, string $entityName, string|null $type)
 * @method \App\Component\Image\Image[] getAllImagesByEntity(object $entity)
 * @method deleteImageFiles(\App\Component\Image\Image $image)
 * @method \App\Component\Image\Image getImageByObject(\App\Component\Image\Image|object $imageOrEntity, string|null $type = null)
 * @method \App\Component\Image\Image getById(int $imageId)
 * @method \App\Component\Image\Image[] getImagesByEntitiesIndexedByEntityId(int[] $entityIds, string $entityClass)
 * @method \App\Component\Image\Image[] getImagesByEntityId(int $id, string $entityClass)
 */
class ImageFacade extends CdnImageFacade
{
    public const AKENEO_MAIN_IMAGE_TYPE = 'image_main';
    public const NOIMAGE_FILENAME = 'noimage.png';
    public const OPTIMIZED_NOIMAGE_FILENAME = 'optimized-' . self::NOIMAGE_FILENAME;

    /**
     * @param mixed $imageUrlPrefix
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \App\Component\Image\ImageRepository $imageRepository
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \App\Component\FileUpload\FileUpload $fileUpload
     * @param \App\Component\Image\ImageLocator $imageLocator
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface $imageFactory
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Symfony\Contracts\Cache\CacheInterface|\Symfony\Component\Cache\Adapter\AdapterInterface $cache
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        $imageUrlPrefix,
        EntityManagerInterface $em,
        ImageConfig $imageConfig,
        ImageRepository $imageRepository,
        FilesystemInterface $filesystem,
        FileUpload $fileUpload,
        ImageLocator $imageLocator,
        ImageFactoryInterface $imageFactory,
        MountManager $mountManager,
        LoggerInterface $logger,
        private readonly CacheInterface|AdapterInterface $cache,
        private readonly Domain $domain,
    ) {
        parent::__construct(
            $imageUrlPrefix,
            $em,
            $imageConfig,
            $imageRepository,
            $filesystem,
            $fileUpload,
            $imageLocator,
            $imageFactory,
            $mountManager,
            $logger
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param object|\App\Component\Image\Image $imageOrEntity
     * @param string|null $sizeName
     * @param string|null $type
     * @return string
     */
    public function getImageUrl(DomainConfig $domainConfig, $imageOrEntity, $sizeName = null, $type = null)
    {
        $image = $this->getImageByObject($imageOrEntity, $type);
        $cacheId = $this->getCacheIdForImageUrl($image->getId(), $domainConfig->getId(), $type, $sizeName);

        return $this->cache->get(
            $cacheId,
            function () use ($image, $domainConfig, $sizeName) {
                if (!$this->imageLocator->imageExists($image)) {
                    throw new ImageNotFoundException();
                }

                $seoEntityName = $this->getSeoNameByImageAndLocale($image, $domainConfig->getLocale());
                $friendlyUrlSeoEntityName = $this->getFriendlyUrlSlug($seoEntityName);

                $imageUrl = $domainConfig->getUrl()
                    . $this->imageUrlPrefix
                    . $this->imageLocator->getRelativeImageFilepathWithSlug($image, $sizeName, $friendlyUrlSeoEntityName);

                return $this->replaceDomainUrlByCdnDomain($imageUrl, $domainConfig);
            }
        );
    }

    /**
     * @param string|null $seoEntityName
     * @return string|null
     */
    private function getFriendlyUrlSlug(?string $seoEntityName): ?string
    {
        if ($seoEntityName === null) {
            return null;
        }

        return TransformString::stringToFriendlyUrlSlug($seoEntityName);
    }

    /**
     * @param \App\Component\Image\Image $image
     * @param string $locale
     * @return string|null
     */
    private function getSeoNameByImageAndLocale(Image $image, string $locale): ?string
    {
        switch ($image->getEntityName()) {
            case 'category':
                $category = $this->em->getRepository(Category::class)->find($image->getEntityId());
                return $category === null ? null : $category->getName($locale);
            case 'product':
                $product = $this->em->getRepository(Product::class)->find($image->getEntityId());
                return $product === null ? null : $product->getName($locale);
            case 'brand':
                $brand = $this->em->getRepository(Brand::class)->find($image->getEntityId());
                return $brand === null ? null : $brand->getName();
            default:
                return null;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $id
     * @param string $extension
     * @param string $entityName
     * @param string|null $type
     * @param string|null $sizeName
     * @return string
     */
    public function getImageUrlFromAttributes(
        DomainConfig $domainConfig,
        int $id,
        string $extension,
        string $entityName,
        ?string $type,
        ?string $sizeName = null
    ): string {
        $image = $this->imageRepository->getById($id);

        return $this->getImageUrl($domainConfig, $image, $sizeName, $type);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \App\Component\Image\Image $imageOrEntity
     * @param string|null $sizeName
     * @param string|null $type
     * @return \App\Component\Image\AdditionalImageData[]
     */
    public function getAdditionalImagesData(DomainConfig $domainConfig, $imageOrEntity, ?string $sizeName, ?string $type): array
    {
        $image = $this->getImageByObject($imageOrEntity, $type);

        $entityConfig = $this->imageConfig->getEntityConfigByEntityName($image->getEntityName());
        $sizeConfig = $entityConfig->getSizeConfigByType($type, $sizeName);

        $result = [];
        foreach ($sizeConfig->getAdditionalSizes() as $additionalSizeIndex => $additionalSizeConfig) {
            $url = $this->getAdditionalImageUrl($domainConfig, $additionalSizeIndex, $image, $sizeName);
            $result[] = new AdditionalImageData(
                $additionalSizeConfig->getMedia(),
                $url,
                $additionalSizeConfig->getWidth(),
                $additionalSizeConfig->getHeight()
            );
        }

        return $result;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $id
     * @param string $extension
     * @param string $entityName
     * @param string|null $type
     * @param string|null $sizeName
     * @return \App\Component\Image\AdditionalImageData[]
     */
    public function getAdditionalImagesDataFromAttributes(
        DomainConfig $domainConfig,
        int $id,
        string $extension,
        string $entityName,
        ?string $type,
        ?string $sizeName = null
    ): array {
        $entityConfig = $this->imageConfig->getEntityConfigByEntityName($entityName);
        $sizeConfig = $entityConfig->getSizeConfigByType($type, $sizeName);

        $result = [];
        foreach ($sizeConfig->getAdditionalSizes() as $additionalSizeIndex => $additionalSizeConfig) {
            $image = $this->imageRepository->getById($id);
            $imageUrl = $this->getAdditionalImageUrl($domainConfig, $additionalSizeIndex, $image, $sizeName);

            $result[] = new AdditionalImageData(
                $additionalSizeConfig->getMedia(),
                $imageUrl,
                $additionalSizeConfig->getWidth(),
                $additionalSizeConfig->getHeight()
            );
        }

        return $result;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $additionalSizeIndex
     * @param \App\Component\Image\Image $image
     * @param string|null $sizeName
     * @return string
     */
    protected function getAdditionalImageUrl(DomainConfig $domainConfig, int $additionalSizeIndex, BaseImage $image, ?string $sizeName)
    {
        $cacheId = $this->getCacheIdForImageUrl(
            $image->getId(),
            $domainConfig->getId(),
            $image->getType(),
            $sizeName,
            $additionalSizeIndex
        );

        return $this->cache->get(
            $cacheId,
            function () use ($image, $domainConfig, $additionalSizeIndex, $sizeName) {
                if (!$this->imageLocator->imageExists($image)) {
                    throw new ImageNotFoundException();
                }

                $seoEntityName = $this->getSeoNameByImageAndLocale($image, $domainConfig->getLocale());
                $friendlyUrlSeoEntityName = $this->getFriendlyUrlSlug($seoEntityName);

                $imageUrl = $domainConfig->getUrl()
                    . $this->imageUrlPrefix
                    . $this->imageLocator->getRelativeAdditionalImageFilepathWithSlug($image, $additionalSizeIndex, $sizeName, $friendlyUrlSeoEntityName);

                return $this->replaceDomainUrlByCdnDomain($imageUrl, $domainConfig);
            }
        );
    }

    /**
     * @param object $entity
     * @param array $temporaryFilenames
     * @param string|null $type
     * @param bool $deleteOldImage
     */
    public function uploadImage($entity, $temporaryFilenames, $type, bool $deleteOldImage = true): void
    {
        $newImage = null;

        if (count($temporaryFilenames) === 0) {
            return;
        }

        $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);
        $entityName = $imageEntityConfig->getEntityName();
        $entityId = $this->getEntityId($entity);
        $oldImage = $this->imageRepository->findImageByEntity($entityName, $entityId, $type);

        if ($oldImage !== null && $deleteOldImage === true) {
            $this->em->remove($oldImage);
        }

        $this->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, $type);

        $newImage = $this->imageFactory->create(
            $imageEntityConfig->getEntityName(),
            $entityId,
            $type,
            array_pop($temporaryFilenames)
        );
        $this->em->persist($newImage);

        $this->em->flush();
    }

    /**
     * @param object $entity
     * @param array|null $temporaryFilenames
     * @param string|null $type
     */
    protected function uploadImages($entity, $temporaryFilenames, $type): void
    {
        if ($temporaryFilenames !== null && count($temporaryFilenames) > 0) {
            $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);
            $entityName = $imageEntityConfig->getEntityName();
            $entityId = $this->getEntityId($entity);

            $images = $this->imageFactory->createMultiple($imageEntityConfig, $entityId, $type, $temporaryFilenames);
            foreach ($images as $image) {
                $this->em->persist($image);
            }

            $this->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, $type);

            $this->em->flush();
        }
    }

    /**
     * @param object $entity
     * @param array $temporaryFilenames
     * @param string|null $type
     * @param bool $deleteOldImage
     * @return \App\Component\Image\Image|null
     */
    public function uploadAndReturnImage($entity, $temporaryFilenames, $type, bool $deleteOldImage = true): ?Image
    {
        $newImage = null;

        if (count($temporaryFilenames) > 0) {
            $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);
            $entityName = $imageEntityConfig->getEntityName();
            $entityId = $this->getEntityId($entity);
            $oldImage = $this->imageRepository->findImageByEntity($imageEntityConfig->getEntityName(), $entityId, $type);

            if ($oldImage !== null && $deleteOldImage === true) {
                $this->em->remove($oldImage);
            }

            $this->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, $type);

            /** @var \App\Component\Image\Image|null $newImage */
            $newImage = $this->imageFactory->create(
                $imageEntityConfig->getEntityName(),
                $entityId,
                $type,
                array_pop($temporaryFilenames)
            );
            $this->em->persist($newImage);

            $this->em->flush();
        }

        return $newImage;
    }

    /**
     * @param mixed $entity
     * @param array $images
     */
    public function deleteImages($entity, array $images): void
    {
        $entityName = $this->imageConfig->getEntityName($entity);
        $entityId = $this->getEntityId($entity);

        // files will be deleted in doctrine listener
        foreach ($images as $image) {
            $image->checkForDelete($entityName, $entityId);
        }

        foreach ($images as $image) {
            $imageToRemove = $this->imageRepository->findById($image->getId());
            $this->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, $image->getType());
            if ($imageToRemove !== null) {
                $this->em->remove($imageToRemove);
            }
        }
        $this->em->flush();
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @return \App\Component\Image\Image
     */
    public function getImageByEntity($entity, $type): BaseImage
    {
        $entityName = $this->imageConfig->getEntityName($entity);
        $entityId = $this->getEntityId($entity);
        $cacheId = $this->getCacheIdForSingleEntity($entityName, $entityId, $type);

        return $this->cache->get(
            $cacheId,
            function () use ($entityName, $entityId, $type) {
                return $this->imageRepository->getImageByEntity(
                    $entityName,
                    $entityId,
                    $type
                );
            }
        );
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @return \App\Component\Image\Image[]
     */
    public function getImagesByEntityIndexedById($entity, $type): array
    {
        $entityName = $this->imageConfig->getEntityName($entity);
        $entityId = $this->getEntityId($entity);

        $cacheId = $this->getCacheIdForMultipleEntities($entityName, $entityId, $type);

        return $this->cache->get(
            $cacheId,
            function () use ($entityName, $entityId, $type) {
                return $this->imageRepository->getImagesByEntityIndexedById(
                    $entityName,
                    $entityId,
                    $type
                );
            }
        );
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData $imageUploadData
     * @param string|null $type
     */
    public function manageImages(object $entity, ImageUploadData $imageUploadData, ?string $type = null): void
    {
        $entityName = $this->imageConfig->getEntityName($entity);
        $entityId = $this->getEntityId($entity);

        $this->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, null);

        parent::manageImages($entity, $imageUploadData, $type);
    }

    /**
     * @param \App\Component\Image\Image[] $orderedImages
     */
    protected function setImagePositionsByOrder($orderedImages)
    {
        $position = 0;
        $canUpdateAkeneoType = false;
        foreach ($orderedImages as $image) {
            $image->setPosition($position);
            $position++;
            if ($image->getEntityName() === 'product') {
                $canUpdateAkeneoType = true;
            }
        }

        if (!$canUpdateAkeneoType) {
            return;
        }

        foreach ($orderedImages as $image) {
            if ($image->getPosition() === 0) {
                $image->setAkeneoImageType(self::AKENEO_MAIN_IMAGE_TYPE);
            } elseif ($image->getAkeneoImageType() === self::AKENEO_MAIN_IMAGE_TYPE) {
                $image->setAkeneoImageType(null);
            }
        }
    }

    /**
     * @param \App\Component\Image\Image[] $orderedImages
     */
    protected function saveImageOrdering($orderedImages): void
    {
        // Image entity can be cached and It caused no persisted entity -> fatal on flush
        $persistedImages = [];
        foreach ($orderedImages as $image) {
            if ($this->em->getUnitOfWork()->isInIdentityMap($image) === true) {
                $persistedImages[] = $image;
            } else {
                $persistedImages[] = $this->getById($image->getId());
            }
        }

        parent::saveImageOrdering($persistedImages);
    }

    /**
     * @return bool
     */
    public function clearImageCache(): bool
    {
        return $this->cache->clear();
    }

    /**
     * @param int $imageId
     * @param int $domainId
     * @param string|null $type
     * @param string|null $sizeName
     * @param int|null $additionalIndex
     * @return string
     */
    private function getCacheIdForImageUrl(
        int $imageId,
        int $domainId,
        ?string $type,
        ?string $sizeName,
        ?int $additionalIndex = null
    ): string {
        return sprintf(
            'ImageUrl_imageId-%d_domainId-%d_type-%s_size-%s_additionalIndex-%s',
            $imageId,
            $domainId,
            $type,
            $sizeName,
            $additionalIndex
        );
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
            return sprintf('cache_image_entity_%s_%d', $entityName, $entityId);
        }

        return sprintf('cache_image_entity_%s_%d_%s', $entityName, $entityId, $type);
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
            return sprintf('cache_images_entities_%s_%d', $entityName, $entityId);
        }

        return sprintf('cache_images_entities_%s_%d_%s', $entityName, $entityId, $type);
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

        $this->cache->delete($cacheIdForSingleEntity);
        $this->cache->delete($cacheIdForMultipleEntities);
    }

    /**
     * @param string|null $content
     * @return string|null
     */
    public function replaceImageUrlsByCdn(?string $content): ?string
    {
        if ($this->cdnDomain === null || $content === null) {
            return $content;
        }

        $escapedDomainUrls = [];
        foreach ($this->domain->getAll() as $domainConfig) {
            $escapedDomainUrls[] = preg_quote($domainConfig->getUrl(), '/');
        }

        $domainsPattern = implode('|', $escapedDomainUrls);
        $pattern = '/((' . $domainsPattern . ')\/)(content|public)/i';

        $replacement = $this->cdnDomain . '/$3';

        return preg_replace($pattern, $replacement, $content);
    }

    /**
     * @return string|null
     */
    public function getCdnDomain(): ?string
    {
        return $this->cdnDomain;
    }
}
