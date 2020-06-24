<?php

declare(strict_types=1);

namespace App\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade as BaseImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository;

/**
 * @property \App\Component\Image\ImageRepository $imageRepository
 * @method saveImageOrdering(\App\Component\Image\Image[] $orderedImages)
 * @method \App\Component\Image\Image[] getAllImagesByEntity(object $entity)
 * @method deleteImageFiles(\App\Component\Image\Image $image)
 * @method \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[] getAdditionalImagesData(\Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig, \App\Component\Image\Image $imageOrEntity, string|null $sizeName, string|null $type)
 * @method \App\Component\Image\Image getImageByObject(\App\Component\Image\Image|object $imageOrEntity, string|null $type)
 * @method \App\Component\Image\Image getById(int $imageId)
 * @method setImagePositionsByOrder(\App\Component\Image\Image[] $orderedImages)
 * @method \App\Component\Image\Image[] getImagesByEntitiesIndexedByEntityId(int[] $entityIds, string $entityClass)
 * @property \App\Component\Image\Config\ImageConfig $imageConfig
 * @method \App\Component\Image\Image[] getImagesByEntityIdAndNameIndexedById(int $entityId, string $entityName, string|null $type)
 */
class ImageFacade extends BaseImageFacade
{
    /**
     * @var string|null
     */
    private $cdnDomain;

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
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageLocator $imageLocator
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
     * @param string $cdnDomain
     */
    public function setCdnDomain(string $cdnDomain): void
    {
        // When you do not want to use CDN, it is used value '//' as workaround by https://github.com/symfony/symfony/issues/28391
        if (empty(trim($cdnDomain, '/')) === false) {
            $this->cdnDomain = $cdnDomain;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \App\Component\Image\Image|Object $imageOrEntity
     * @param string|null $sizeName
     * @param string|null $type
     * @return string
     */
    public function getImageUrl(DomainConfig $domainConfig, $imageOrEntity, $sizeName = null, $type = null)
    {
        $imageUrl = parent::getImageUrl($domainConfig, $imageOrEntity, $sizeName, $type);

        return $this->replaceDomainUrlByCdnDomain($imageUrl, $domainConfig);
    }

    /**
     * @param int $productId
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string|null $sizeName
     * @param string|null $type
     * @return string
     */
    public function getProductImageUrlByProductId(int $productId, DomainConfig $domainConfig, ?string $sizeName = null, ?string $type = null): string
    {
        $image = $this->imageRepository->getImageByEntity(
            'product',
            $productId,
            $type
        );

        return $this->getImageUrl($domainConfig, $image, $sizeName, $type);
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
        $imageUrl = parent::getImageUrlFromAttributes($domainConfig, $id, $extension, $entityName, $type, $sizeName);

        return $this->replaceDomainUrlByCdnDomain($imageUrl, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $id
     * @param string $extension
     * @param string $entityName
     * @param string|null $type
     * @param string|null $sizeName
     * @return \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[]
     */
    public function getAdditionalImagesDataFromAttributes(
        DomainConfig $domainConfig,
        int $id,
        string $extension,
        string $entityName,
        ?string $type,
        ?string $sizeName = null
    ): array {
        $additionalImagesData = parent::getAdditionalImagesDataFromAttributes($domainConfig, $id, $extension, $entityName, $type, $sizeName);

        return $this->replaceDomainUrlByCdnDomainInAdditionalImagesData($additionalImagesData, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $additionalSizeIndex
     * @param \App\Component\Image\Image $image
     * @param string|null $sizeName
     * @return string
     */
    protected function getAdditionalImageUrl(DomainConfig $domainConfig, int $additionalSizeIndex, Image $image, ?string $sizeName)
    {
        $imageUrl = parent::getAdditionalImageUrl($domainConfig, $additionalSizeIndex, $image, $sizeName);

        return $this->replaceDomainUrlByCdnDomain($imageUrl, $domainConfig);
    }

    /**
     * @param string $imageUrl
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    private function replaceDomainUrlByCdnDomain(string $imageUrl, DomainConfig $domainConfig): string
    {
        if ($this->cdnDomain === null) {
            return $imageUrl;
        }

        return str_replace($domainConfig->getUrl(), $this->cdnDomain, $imageUrl);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[] $additionalImagesData
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[]
     */
    private function replaceDomainUrlByCdnDomainInAdditionalImagesData(array $additionalImagesData, DomainConfig $domainConfig): array
    {
        if ($this->cdnDomain === null) {
            return $additionalImagesData;
        }

        foreach ($additionalImagesData as $additionalImageData) {
            $additionalImageData->url = $this->replaceDomainUrlByCdnDomain($additionalImageData->url, $domainConfig);
        }

        return $additionalImagesData;
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

        if (count($temporaryFilenames) > 0) {
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
    public function uploadAndReturnImage($entity, $temporaryFilenames, $type, bool $deleteOldImage = true): ?\App\Component\Image\Image
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
     * @return \App\Component\Image\Image[]|null
     */
    public function findImagesForKrakenOptimization(): ?array
    {
        return $this->imageRepository->findImagesForKrakenOptimization();
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @return \App\Component\Image\Image
     */
    public function getImageByEntity($entity, $type): Image
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
    public function getImageByObjectAndAkeneoType(object $entity, string $akeneoImageType): Image
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
}
