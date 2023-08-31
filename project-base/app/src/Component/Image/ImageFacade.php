<?php

declare(strict_types=1);

namespace App\Component\Image;

use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Image as BaseImage;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade as BaseImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @property \App\Component\Image\ImageRepository $imageRepository
 * @property \App\Component\FileUpload\FileUpload $fileUpload
 * @property \App\Component\Image\ImageLocator $imageLocator
 * @method \App\Component\Image\Image[] getImagesByEntityIdAndNameIndexedById(int $entityId, string $entityName, string|null $type)
 * @method \App\Component\Image\Image[] getAllImagesByEntity(object $entity)
 * @method deleteImageFiles(\App\Component\Image\Image $image)
 * @method \App\Component\Image\Image getImageByObject(object $imageOrEntity, string|null $type = null)
 * @method \App\Component\Image\Image getById(int $imageId)
 * @method \App\Component\Image\Image[] getImagesByEntitiesIndexedByEntityId(int[] $entityIds, string $entityClass)
 * @method \App\Component\Image\Image[] getImagesByEntityId(int $id, string $entityClass)
 * @method \App\Component\Image\Image getImageByEntity(object $entity, string|null $type)
 * @method \App\Component\Image\Image[] getImagesByEntityIndexedById(object $entity, string|null $type)
 */
class ImageFacade extends BaseImageFacade
{
    public const AKENEO_MAIN_IMAGE_TYPE = 'image_main';
    public const NOIMAGE_FILENAME = 'noimage.png';
    public const OPTIMIZED_NOIMAGE_FILENAME = 'optimized-' . self::NOIMAGE_FILENAME;

    /**
     * @param string $imageUrlPrefix
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \App\Component\Image\ImageRepository $imageRepository
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \App\Component\FileUpload\FileUpload $fileUpload
     * @param \App\Component\Image\ImageLocator $imageLocator
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFactory $imageFactory
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Shopsys\FrameworkBundle\Component\Cdn\CdnFacade $cdnFacade
     * @param \Symfony\Contracts\Cache\CacheInterface|\Symfony\Component\Cache\Adapter\AdapterInterface $cache
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        string $imageUrlPrefix,
        EntityManagerInterface $em,
        ImageConfig $imageConfig,
        ImageRepository $imageRepository,
        FilesystemOperator $filesystem,
        FileUpload $fileUpload,
        ImageLocator $imageLocator,
        ImageFactoryInterface $imageFactory,
        MountManager $mountManager,
        LoggerInterface $logger,
        CdnFacade $cdnFacade,
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
            $logger,
            $cdnFacade,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param object $imageOrEntity
     * @param string|null $sizeName
     * @param string|null $type
     * @return string
     */
    public function getImageUrl(
        DomainConfig $domainConfig,
        object $imageOrEntity,
        ?string $sizeName = null,
        ?string $type = null,
    ): string {
        $image = $this->getImageByObject($imageOrEntity, $type);
        $cacheId = $this->getCacheIdForImageUrl($image->getId(), $domainConfig->getId());

        $friendlyUrlSeoEntityName = $this->cache->get(
            $cacheId,
            function () use ($image, $domainConfig) {
                if (!$this->imageLocator->imageExists($image)) {
                    throw new ImageNotFoundException();
                }

                $seoEntityName = $this->getSeoNameByImageAndLocale($image, $domainConfig->getLocale());

                return $this->getFriendlyUrlSlug($seoEntityName);
            },
        );

        return $this->cdnFacade->resolveDomainUrlForAssets($domainConfig)
            . $this->imageUrlPrefix
            . $this->imageLocator->getRelativeImageFilepathWithSlug($image, $sizeName, $friendlyUrlSeoEntityName);
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

                return $category?->getName($locale);
            case 'product':
                $product = $this->em->getRepository(Product::class)->find($image->getEntityId());

                return $product?->getName($locale);
            case 'brand':
                $brand = $this->em->getRepository(Brand::class)->find($image->getEntityId());

                return $brand?->getName();
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
        ?string $sizeName = null,
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
    public function getAdditionalImagesData(
        DomainConfig $domainConfig,
        $imageOrEntity,
        ?string $sizeName,
        ?string $type,
    ): array {
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
                $additionalSizeConfig->getHeight(),
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
        ?string $sizeName = null,
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
                $additionalSizeConfig->getHeight(),
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
    protected function getAdditionalImageUrl(
        DomainConfig $domainConfig,
        int $additionalSizeIndex,
        BaseImage $image,
        ?string $sizeName,
    ): string {
        $cacheId = $this->getCacheIdForImageUrl(
            $image->getId(),
            $domainConfig->getId(),
            $additionalSizeIndex,
        );

        $friendlyUrlSeoEntityName = $this->cache->get(
            $cacheId,
            function () use ($image, $domainConfig) {
                if (!$this->imageLocator->imageExists($image)) {
                    throw new ImageNotFoundException();
                }

                $seoEntityName = $this->getSeoNameByImageAndLocale($image, $domainConfig->getLocale());

                return $this->getFriendlyUrlSlug($seoEntityName);
            },
        );

        return $this->cdnFacade->resolveDomainUrlForAssets($domainConfig)
            . $this->imageUrlPrefix
            . $this->imageLocator->getRelativeAdditionalImageFilepathWithSlug($image, $additionalSizeIndex, $sizeName, $friendlyUrlSeoEntityName);
    }

    /**
     * @param object $entity
     * @param array $temporaryFilenames
     * @param string|null $type
     * @param bool $deleteOldImage
     * @return \App\Component\Image\Image|null
     */
    public function uploadAndReturnImage(
        object $entity,
        array $temporaryFilenames,
        ?string $type,
        bool $deleteOldImage = true,
    ): ?Image {
        $newImage = null;

        if (count($temporaryFilenames) > 0) {
            $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);
            $entityName = $imageEntityConfig->getEntityName();
            $entityId = $this->getEntityId($entity);
            $oldImage = $this->imageRepository->findImageByEntity($imageEntityConfig->getEntityName(), $entityId, $type);
            $generatedNamesIndexedByLocale = [];

            foreach ($this->domain->getAllLocales() as $locale) {
                $generatedNamesIndexedByLocale[$locale] = sprintf('%s - %d (%s)', $entityName, $entityId, $locale);
            }

            if ($oldImage !== null && $deleteOldImage === true) {
                $this->em->remove($oldImage);
            }

            /** @var \App\Component\Image\Image|null $newImage */
            $newImage = $this->imageFactory->create(
                $imageEntityConfig->getEntityName(),
                $entityId,
                $generatedNamesIndexedByLocale,
                array_pop($temporaryFilenames),
                $type,
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

            if ($imageToRemove !== null) {
                $this->em->remove($imageToRemove);
            }
        }
        $this->em->flush();
    }

    /**
     * @param \App\Component\Image\Image[] $orderedImages
     */
    protected function setImagePositionsByOrder(array $orderedImages): void
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
        // Image entity can be cached, and It caused no persisted entity -> fatal on flush
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
     * @param int $imageId
     * @param int $domainId
     * @param int|null $additionalIndex
     * @return string
     */
    private function getCacheIdForImageUrl(
        int $imageId,
        int $domainId,
        ?int $additionalIndex = null,
    ): string {
        return sprintf(
            'ImageUrl_imageId-%d_domainId-%d_type-%s',
            $imageId,
            $domainId,
            $additionalIndex,
        );
    }
}
