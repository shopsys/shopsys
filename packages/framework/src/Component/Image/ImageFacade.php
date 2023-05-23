<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use League\Flysystem\UnableToDeleteFile;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\EntityIdentifierException;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\String\TransformString;

class ImageFacade
{
    /**
     * @param string $imageUrlPrefix
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageRepository $imageRepository
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageLocator $imageLocator
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface $imageFactory
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Shopsys\FrameworkBundle\Component\Cdn\CdnFacade $cdnFacade
     */
    public function __construct(
        protected readonly string $imageUrlPrefix,
        protected readonly EntityManagerInterface $em,
        protected readonly ImageConfig $imageConfig,
        protected readonly ImageRepository $imageRepository,
        protected readonly FilesystemOperator $filesystem,
        protected readonly FileUpload $fileUpload,
        protected readonly ImageLocator $imageLocator,
        protected readonly ImageFactoryInterface $imageFactory,
        protected readonly MountManager $mountManager,
        protected readonly LoggerInterface $logger,
        protected readonly CdnFacade $cdnFacade,
    ) {
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData $imageUploadData
     * @param string|null $type
     */
    public function manageImages(object $entity, ImageUploadData $imageUploadData, ?string $type = null): void
    {
        $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);
        $uploadedFiles = $imageUploadData->uploadedFiles;
        $orderedImages = $imageUploadData->orderedImages;

        if ($imageEntityConfig->isMultiple($type) === false) {
            if (count($orderedImages) > 1) {
                array_shift($orderedImages);
                $this->deleteImages($entity, $orderedImages);
            }
            $this->uploadImage($entity, $imageUploadData->uploadedFilenames, $uploadedFiles, $type);

            if (count($uploadedFiles) === 0) {
                $this->saveImagesPathnames($imageUploadData);
            }
        } else {
            $this->saveImageOrdering($orderedImages);
            $this->saveImagesPathnames($imageUploadData);
            $this->uploadImages($entity, $imageUploadData->uploadedFilenames, $uploadedFiles, $type);
        }

        $this->deleteImages($entity, $imageUploadData->imagesToDelete);
    }

    /**
     * @param object $entity
     * @param array<int, array<string,string>> $namesIndexedByImageIdAndLocale
     * @param array<int, string> $temporaryFilenamesIndexedByImageId
     * @param string|null $type
     */
    protected function uploadImage(
        object $entity,
        array $namesIndexedByImageIdAndLocale,
        array $temporaryFilenamesIndexedByImageId,
        ?string $type,
    ): void {
        if (count($temporaryFilenamesIndexedByImageId) > 0) {
            $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);
            $entityId = $this->getEntityId($entity);
            $oldImage = $this->imageRepository->findImageByEntity(
                $imageEntityConfig->getEntityName(),
                $entityId,
                $type,
            );

            if ($oldImage !== null) {
                $this->em->remove($oldImage);
            }

            $newImage = $this->imageFactory->create(
                $imageEntityConfig->getEntityName(),
                $entityId,
                array_pop($namesIndexedByImageIdAndLocale),
                array_pop($temporaryFilenamesIndexedByImageId),
                $type,
            );
            $this->em->persist($newImage);

            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $orderedImages
     */
    protected function saveImageOrdering(array $orderedImages): void
    {
        $this->setImagePositionsByOrder($orderedImages);
        $this->em->flush();
    }

    /**
     * @param object $entity
     * @param array<int, array<string,string>> $namesIndexedByImageIdAndLocale
     * @param array<int, string> $temporaryFilenamesIndexedByImageId
     * @param string|null $type
     */
    protected function uploadImages(
        object $entity,
        array $namesIndexedByImageIdAndLocale,
        ?array $temporaryFilenamesIndexedByImageId,
        ?string $type,
    ): void {
        if ($temporaryFilenamesIndexedByImageId !== null && count($temporaryFilenamesIndexedByImageId) > 0) {
            $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);
            $entityId = $this->getEntityId($entity);

            $images = $this->imageFactory->createMultiple($imageEntityConfig, $entityId, $namesIndexedByImageIdAndLocale, $temporaryFilenamesIndexedByImageId, $type);

            foreach ($images as $image) {
                $this->em->persist($image);
            }
            $this->em->flush();
        }
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $images
     */
    protected function deleteImages(object $entity, array $images): void
    {
        $entityName = $this->imageConfig->getEntityName($entity);
        $entityId = $this->getEntityId($entity);

        // files will be deleted in doctrine listener
        foreach ($images as $image) {
            $image->checkForDelete($entityName, $entityId);
        }

        foreach ($images as $image) {
            $this->em->remove($image);
        }
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image
     */
    public function getImageByEntity(object $entity, ?string $type): Image
    {
        return $this->imageRepository->getImageByEntity(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $type,
        );
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImagesByEntityIndexedById(object $entity, ?string $type): array
    {
        return $this->imageRepository->getImagesByEntityIndexedById(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $type,
        );
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
     * @param object $entity
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getAllImagesByEntity(object $entity): array
    {
        return $this->imageRepository->getAllImagesByEntity(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     */
    public function deleteImageFiles(Image $image): void
    {
        $entityName = $image->getEntityName();
        $imageConfig = $this->imageConfig->getEntityConfigByEntityName($entityName);
        $sizeConfigs = $image->getType() === null ? $imageConfig->getSizeConfigs() : $imageConfig->getSizeConfigsByType(
            $image->getType(),
        );

        foreach ($sizeConfigs as $sizeConfig) {
            $filepath = $this->imageLocator->getAbsoluteImageFilepath($image, $sizeConfig->getName());

            if ($this->filesystem->has($filepath)) {
                $this->filesystem->delete($filepath);
            }
        }
    }

    /**
     * @param object $entity
     * @return int
     */
    protected function getEntityId(object $entity): int
    {
        $entityMetadata = $this->em->getClassMetadata(get_class($entity));
        $identifier = $entityMetadata->getIdentifierValues($entity);

        if (count($identifier) === 1) {
            return array_pop($identifier);
        }

        $message = 'Entity "' . get_class($entity) . '" has not set primary key or primary key is compound."';

        throw new EntityIdentifierException($message);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[]
     */
    public function getAllImageEntityConfigsByClass(): array
    {
        return $this->imageConfig->getAllImageEntityConfigsByClass();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param object $imageOrEntity
     * @param string|null $sizeName
     * @param string|null $type
     * @return string
     */
    public function getImageUrl(DomainConfig $domainConfig, object $imageOrEntity, ?string $sizeName = null, ?string $type = null): string
    {
        $image = $this->getImageByObject($imageOrEntity, $type);

        if (!$this->imageLocator->imageExists($image)) {
            throw new ImageNotFoundException();
        }

        return $this->cdnFacade->resolveDomainUrlForAssets($domainConfig)
            . $this->imageUrlPrefix
            . $this->imageLocator->getRelativeImageFilepath($image, $sizeName);
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
        $imageFilepath = $this->imageLocator->getRelativeImageFilepathFromAttributes(
            $id,
            $extension,
            $entityName,
            $type,
            $sizeName,
        );

        return $this->cdnFacade->resolveDomainUrlForAssets($domainConfig) . $this->imageUrlPrefix . $imageFilepath;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param object $imageOrEntity
     * @param string|null $sizeName
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[]
     */
    public function getAdditionalImagesData(
        DomainConfig $domainConfig,
        object $imageOrEntity,
        ?string $sizeName,
        ?string $type,
    ) {
        $image = $this->getImageByObject($imageOrEntity, $type);

        $entityConfig = $this->imageConfig->getEntityConfigByEntityName($image->getEntityName());
        $sizeConfig = $entityConfig->getSizeConfigByType($type, $sizeName);

        $result = [];

        foreach ($sizeConfig->getAdditionalSizes() as $additionalSizeIndex => $additionalSizeConfig) {
            $url = $this->getAdditionalImageUrl($domainConfig, $additionalSizeIndex, $image, $sizeName);
            $result[] = new AdditionalImageData($additionalSizeConfig->getMedia(), $url);
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
     * @return \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[]
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

        $resolvedImageUrl = $this->cdnFacade->resolveDomainUrlForAssets($domainConfig);

        $result = [];

        foreach ($sizeConfig->getAdditionalSizes() as $additionalSizeIndex => $additionalSizeConfig) {
            $imageFilepath = $this->imageLocator->getRelativeImageFilepathFromAttributes(
                $id,
                $extension,
                $entityName,
                $type,
                $sizeName,
                $additionalSizeIndex,
            );
            $url = $resolvedImageUrl . $this->imageUrlPrefix . $imageFilepath;

            $result[] = new AdditionalImageData($additionalSizeConfig->getMedia(), $url);
        }

        return $result;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $additionalSizeIndex
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @param string|null $sizeName
     * @return string
     */
    protected function getAdditionalImageUrl(DomainConfig $domainConfig, int $additionalSizeIndex, Image $image, ?string $sizeName): string
    {
        if (!$this->imageLocator->imageExists($image)) {
            throw new ImageNotFoundException();
        }

        return $this->cdnFacade->resolveDomainUrlForAssets($domainConfig)
            . $this->imageUrlPrefix
            . $this->imageLocator->getRelativeAdditionalImageFilepath($image, $additionalSizeIndex, $sizeName);
    }

    /**
     * @param object $imageOrEntity
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image
     */
    public function getImageByObject(object $imageOrEntity, ?string $type = null): Image
    {
        if ($imageOrEntity instanceof Image) {
            return $imageOrEntity;
        }

        return $this->getImageByEntity($imageOrEntity, $type);
    }

    /**
     * @param int $imageId
     * @return \Shopsys\FrameworkBundle\Component\Image\Image
     */
    public function getById(int $imageId): Image
    {
        return $this->imageRepository->getById($imageId);
    }

    /**
     * @param object $sourceEntity
     * @param object $targetEntity
     */
    public function copyImages(object $sourceEntity, object $targetEntity): void
    {
        $sourceImages = $this->getAllImagesByEntity($sourceEntity);

        foreach ($sourceImages as $sourceImage) {
            try {
                $this->mountManager->copy(
                    'main://' . $this->imageLocator->getAbsoluteImageFilepath(
                        $sourceImage,
                        ImageConfig::ORIGINAL_SIZE_NAME,
                    ),
                    'main://' . TransformString::removeDriveLetterFromPath(
                        $this->fileUpload->getTemporaryFilepath($sourceImage->getFilename()),
                    ),
                );
            } catch (UnableToDeleteFile $exception) {
                $this->logger->error('Image could not be copied because file was not found', [$exception]);

                continue;
            }

            $targetImage = $this->imageFactory->create(
                $this->imageConfig->getImageEntityConfig($targetEntity)->getEntityName(),
                $this->getEntityId($targetEntity),
                $sourceImage->getNames(),
                $sourceImage->getType(),
                $sourceImage->getFilename(),
            );

            $this->em->persist($targetImage);
        }
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $orderedImages
     */
    protected function setImagePositionsByOrder(array $orderedImages): void
    {
        $position = 0;

        foreach ($orderedImages as $image) {
            $image->setPosition($position);
            $position++;
        }
    }

    /**
     * @param int[] $entityIds
     * @param string $entityClass FQCN
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImagesByEntitiesIndexedByEntityId(array $entityIds, string $entityClass): array
    {
        $entityName = $this->imageConfig->getImageEntityConfigByClass($entityClass)->getEntityName();

        return $this->imageRepository->getMainImagesByEntitiesIndexedByEntityId($entityIds, $entityName);
    }

    /**
     * @param int $id
     * @param string $entityClass
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImagesByEntityId(int $id, string $entityClass): array
    {
        $entityName = $this->imageConfig->getImageEntityConfigByClass($entityClass)->getEntityName();

        return $this->getImagesByEntityIdAndNameIndexedById($id, $entityName, null);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData $imageUploadData
     */
    protected function saveImagesPathnames(ImageUploadData $imageUploadData): void
    {
        foreach ($imageUploadData->namesIndexedByImageIdAndLocale as $imageId => $filenamesIndexedByLocale) {
            $image = $this->getById($imageId);
            $image->setNames($filenamesIndexedByLocale);
        }

        $this->em->flush();
    }
}
