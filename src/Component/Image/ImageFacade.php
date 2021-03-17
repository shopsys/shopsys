<?php

declare(strict_types=1);

namespace App\Component\Image;

use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Product;
use App\Twig\ImageExtension;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Image as BaseImage;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade as BaseImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository;
use Shopsys\FrameworkBundle\Component\String\TransformString;

/**
 * @property \App\Component\Image\Config\ImageConfig $imageConfig
 * @property \App\Component\Image\ImageRepository $imageRepository
 * @property \App\Component\FileUpload\FileUpload $fileUpload
 * @property \App\Component\Image\ImageLocator $imageLocator
 * @method \App\Component\Image\Image[] getImagesByEntityIdAndNameIndexedById(int $entityId, string $entityName, string|null $type)
 * @method \App\Component\Image\Image[] getAllImagesByEntity(object $entity)
 * @method deleteImageFiles(\App\Component\Image\Image $image)
 * @method \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[] getAdditionalImagesData(\Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig, \App\Component\Image\Image $imageOrEntity, string|null $sizeName, string|null $type)
 * @method \App\Component\Image\Image getImageByObject(\App\Component\Image\Image|object $imageOrEntity, string|null $type)
 * @method \App\Component\Image\Image getById(int $imageId)
 * @method \App\Component\Image\Image[] getImagesByEntitiesIndexedByEntityId(int[] $entityIds, string $entityClass)
 * @method \App\Component\Image\Image[] getImagesByEntityId(int $id, string $entityClass)
 */
class ImageFacade extends BaseImageFacade
{
    public const AKENEO_MAIN_IMAGE_TYPE = 'image_main';

    /**
     * @var \App\Component\Image\ImageCacheFacade
     */
    private $imageCacheFacade;

    /**
     * @param mixed $imageUrlPrefix
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Component\Image\Config\ImageConfig $imageConfig
     * @param \App\Component\Image\ImageRepository $imageRepository
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \App\Component\FileUpload\FileUpload $fileUpload
     * @param \App\Component\Image\ImageLocator $imageLocator
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface $imageFactory
     * @param \League\Flysystem\MountManager $mountManager
     * @param \App\Component\Image\ImageCacheFacade $imageCacheFacade
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
        ImageCacheFacade $imageCacheFacade
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
            $mountManager
        );

        $this->imageCacheFacade = $imageCacheFacade;
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

        $imageUrl = $this->imageCacheFacade->findImageUrlInCache($image->getId(), $type, $sizeName);
        if ($imageUrl !== null) {
            return $imageUrl;
        }

        $seoEntityName = $this->getSeoNameByImageAndLocale($image, $domainConfig->getLocale());
        $friendlyUrlSeoEntityName = $this->getFriendlyUrlSlug($seoEntityName);

        if (!$this->imageLocator->imageExists($image)) {
            throw new ImageNotFoundException();
        }

        $imageUrl = $domainConfig->getUrl()
            . $this->imageUrlPrefix
            . $this->imageLocator->getRelativeImageFilepathWithSlug($image, $sizeName, $friendlyUrlSeoEntityName);

        $this->imageCacheFacade->setImageUrlIntoCache($imageUrl, $image->getId(), $type, $sizeName);

        return $imageUrl;
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
     * @param int $additionalSizeIndex
     * @param \App\Component\Image\Image $image
     * @param string|null $sizeName
     * @return string
     */
    protected function getAdditionalImageUrl(DomainConfig $domainConfig, int $additionalSizeIndex, BaseImage $image, ?string $sizeName)
    {
        if (!$this->imageLocator->imageExists($image)) {
            throw new ImageNotFoundException();
        }

        $imageUrl = $this->imageCacheFacade->findImageUrlInCache(
            $image->getId(),
            $image->getType(),
            $sizeName,
            $additionalSizeIndex
        );
        if ($imageUrl !== null) {
            return $imageUrl;
        }

        $seoEntityName = $this->getSeoNameByImageAndLocale($image, $domainConfig->getLocale());
        $friendlyUrlSeoEntityName = $this->getFriendlyUrlSlug($seoEntityName);

        $imageUrl = $domainConfig->getUrl()
            . $this->imageUrlPrefix
            . $this->imageLocator->getRelativeAdditionalImageFilepathWithSlug($image, $additionalSizeIndex, $sizeName, $friendlyUrlSeoEntityName);

        $this->imageCacheFacade->setImageUrlIntoCache(
            $imageUrl,
            $image->getId(),
            $image->getType(),
            $sizeName,
            $additionalSizeIndex
        );

        return $imageUrl;
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
        $this->imageCacheFacade->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, $type);

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

            $this->imageCacheFacade->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, $type);

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
            $this->imageCacheFacade->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, $type);

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
            $this->imageCacheFacade->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, $image->getType());
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
        $image = $this->imageCacheFacade->findCachedImageEntityByEntityNameAndEntityIdAndType($entityName, $entityId, $type);

        if ($image !== null) {
            return $image;
        }

        $image = $this->imageRepository->getImageByEntity(
            $entityName,
            $entityId,
            $type
        );

        $this->imageCacheFacade->setImageEntityIntoCacheByEntityNameAndEntityIdAndType($entityName, $entityId, $type, $image);

        return $image;
    }

    /**
     * @param object $entity
     * @param string $akeneoImageType
     * @return \App\Component\Image\Image
     */
    public function getImageByObjectAndAkeneoType(object $entity, string $akeneoImageType): BaseImage
    {
        $entityName = $this->imageConfig->getEntityName($entity);
        $entityId = $this->getEntityId($entity);

        $image = $this->imageCacheFacade->findCachedImageEntityByEntityNameAndEntityIdAndType($entityName, $entityId, $akeneoImageType);

        if ($image !== null) {
            return $image;
        }

        $image = $this->imageRepository->findImageByEntityForAkeneoImageType($entityName, $entityId, $akeneoImageType);
        if ($image === null) {
            throw new ImageNotFoundException();
        }

        $this->imageCacheFacade->setImageEntityIntoCacheByEntityNameAndEntityIdAndType($entityName, $entityId, $akeneoImageType, $image);

        return $image;
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
        $images = $this->imageCacheFacade->findCachedImageEntitiesByEntityNameAndEntityIdAndType($entityName, $entityId, $type);

        if ($images !== null) {
            return $images;
        }

        $imagesByEntity = $this->imageRepository->getImagesByEntityIndexedById(
            $entityName,
            $entityId,
            $type
        );

        $this->imageCacheFacade->setImageEntitiesIntoCacheByEntityNameAndEntityIdAndType($entityName, $entityId, $type, $imagesByEntity);

        return $imagesByEntity;
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
        $this->imageCacheFacade->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, null);

        parent::manageImages($entity, $imageUploadData, $type);
    }

    /**
     * @param string $emptyImageUrl
     * @return string
     */
    public function getEmptyImageUrl(string $emptyImageUrl): string
    {
        return str_replace(ImageExtension::NOIMAGE_FILENAME, ImageExtension::OPTIMIZED_NOIMAGE_FILENAME, $emptyImageUrl);
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
}
