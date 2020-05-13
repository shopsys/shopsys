<?php

declare(strict_types=1);

namespace App\Component\Image;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade as BaseImageFacade;

/**
 * @property \App\Component\Image\ImageRepository $imageRepository
 * @method __construct(mixed $imageUrlPrefix, \Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig, \App\Component\Image\ImageRepository $imageRepository, \League\Flysystem\FilesystemInterface $filesystem, \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload, \Shopsys\FrameworkBundle\Component\Image\ImageLocator $imageLocator, \Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface $imageFactory, \League\Flysystem\MountManager $mountManager)
 * @method saveImageOrdering(\App\Component\Image\Image[] $orderedImages)
 * @method deleteImages(object $entity, \App\Component\Image\Image[] $images)
 * @method \App\Component\Image\Image getImageByEntity(object $entity, string|null $type)
 * @method \App\Component\Image\Image[] getImagesByEntityIndexedById(object $entity, string|null $type)
 * @method \App\Component\Image\Image[] getImagesByEntityIdAndNameIndexedById(int $entityId, string $entityName, string|null $type)
 * @method \App\Component\Image\Image[] getAllImagesByEntity(object $entity)
 * @method deleteImageFiles(\App\Component\Image\Image $image)
 * @method \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[] getAdditionalImagesData(\Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig, \App\Component\Image\Image $imageOrEntity, string|null $sizeName, string|null $type)
 * @method \App\Component\Image\Image getImageByObject(\App\Component\Image\Image|object $imageOrEntity, string|null $type)
 * @method \App\Component\Image\Image getById(int $imageId)
 * @method setImagePositionsByOrder(\App\Component\Image\Image[] $orderedImages)
 * @method \App\Component\Image\Image[] getImagesByEntitiesIndexedByEntityId(int[] $entityIds, string $entityClass)
 */
class ImageFacade extends BaseImageFacade
{
    /**
     * @var string|null
     */
    private $cdnDomain;

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
            $entityId = $this->getEntityId($entity);
            $oldImage = $this->imageRepository->findImageByEntity($imageEntityConfig->getEntityName(), $entityId, $type);

            if ($oldImage !== null && $deleteOldImage === true) {
                $this->em->remove($oldImage);
            }

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
     * @param string $akeneoImageType
     * @throws \Shopsys\FrameworkBundle\Component\Image\Exception\EntityIdentifierException
     * @return \App\Component\Image\Image|null
     */
    public function findImageByEntityForAkeneoImageType($entity, string $akeneoImageType): ?Image
    {
        return $this->imageRepository->findImageByEntityForAkeneoImageType(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $akeneoImageType
        );
    }
}
