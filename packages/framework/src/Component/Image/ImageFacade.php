<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use BadMethodCallException;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Psr\Log\LoggerInterface;
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
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     */
    protected $imageConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageRepository
     */
    protected $imageRepository;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \League\Flysystem\MountManager
     */
    protected $mountManager;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload
     */
    protected $fileUpload;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageLocator
     */
    protected $imageLocator;

    /**
     * @var string
     */
    protected $imageUrlPrefix;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface
     */
    protected $imageFactory;

    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    protected ?LoggerInterface $logger;

    /**
     * @param mixed $imageUrlPrefix
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageRepository $imageRepository
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageLocator $imageLocator
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface $imageFactory
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Psr\Log\LoggerInterface|null $logger
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
        ?LoggerInterface $logger = null
    ) {
        $this->imageUrlPrefix = $imageUrlPrefix;
        $this->em = $em;
        $this->imageConfig = $imageConfig;
        $this->imageRepository = $imageRepository;
        $this->filesystem = $filesystem;
        $this->fileUpload = $fileUpload;
        $this->imageLocator = $imageLocator;
        $this->imageFactory = $imageFactory;
        $this->mountManager = $mountManager;
        $this->logger = $logger;
    }

    /**
     * @required
     * @param \Psr\Log\LoggerInterface $logger
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setLogger(LoggerInterface $logger): void
    {
        if (
            $this->logger !== null
            && $this->logger !== $logger
        ) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->logger !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->logger = $logger;
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
            $this->uploadImage($entity, $uploadedFiles, $type);
        } else {
            $this->saveImageOrdering($orderedImages);
            $this->uploadImages($entity, $uploadedFiles, $type);
        }

        $this->deleteImages($entity, $imageUploadData->imagesToDelete);
    }

    /**
     * @param object $entity
     * @param array $temporaryFilenames
     * @param string|null $type
     */
    protected function uploadImage($entity, $temporaryFilenames, $type): void
    {
        if (count($temporaryFilenames) > 0) {
            $entitiesForFlush = [];
            $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);
            $entityId = $this->getEntityId($entity);
            $oldImage = $this->imageRepository->findImageByEntity(
                $imageEntityConfig->getEntityName(),
                $entityId,
                $type
            );

            if ($oldImage !== null) {
                $this->em->remove($oldImage);
                $entitiesForFlush[] = $oldImage;
            }

            $newImage = $this->imageFactory->create(
                $imageEntityConfig->getEntityName(),
                $entityId,
                $type,
                array_pop($temporaryFilenames)
            );
            $this->em->persist($newImage);
            $entitiesForFlush[] = $newImage;

            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $orderedImages
     */
    protected function saveImageOrdering($orderedImages): void
    {
        $this->setImagePositionsByOrder($orderedImages);
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
            $entityId = $this->getEntityId($entity);

            $images = $this->imageFactory->createMultiple($imageEntityConfig, $entityId, $type, $temporaryFilenames);
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
    protected function deleteImages($entity, array $images): void
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
    public function getImageByEntity($entity, $type)
    {
        return $this->imageRepository->getImageByEntity(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $type
        );
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImagesByEntityIndexedById($entity, $type)
    {
        return $this->imageRepository->getImagesByEntityIndexedById(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $type
        );
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     * @deprecated This method will be removed in next major version. It was used only in FE API, so it has been replaced by \Shopsys\FrontendApiBundle\Component\Image\ImageFacade::getImagesByEntityIdAndNameIndexedById()
     */
    public function getImagesByEntityIdAndNameIndexedById(int $entityId, string $entityName, $type)
    {
        return $this->imageRepository->getImagesByEntityIndexedById(
            $entityName,
            $entityId,
            $type
        );
    }

    /**
     * @param object $entity
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getAllImagesByEntity($entity)
    {
        return $this->imageRepository->getAllImagesByEntity(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity)
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     */
    public function deleteImageFiles(Image $image)
    {
        $entityName = $image->getEntityName();
        $imageConfig = $this->imageConfig->getEntityConfigByEntityName($entityName);
        $sizeConfigs = $image->getType() === null ? $imageConfig->getSizeConfigs() : $imageConfig->getSizeConfigsByType(
            $image->getType()
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
    protected function getEntityId($entity)
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
    public function getAllImageEntityConfigsByClass()
    {
        return $this->imageConfig->getAllImageEntityConfigsByClass();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|object $imageOrEntity
     * @param string|null $sizeName
     * @param string|null $type
     * @return string
     */
    public function getImageUrl(DomainConfig $domainConfig, $imageOrEntity, $sizeName = null, $type = null)
    {
        $image = $this->getImageByObject($imageOrEntity, $type);
        if ($this->imageLocator->imageExists($image)) {
            return $domainConfig->getUrl()
                . $this->imageUrlPrefix
                . $this->imageLocator->getRelativeImageFilepath($image, $sizeName);
        }

        throw new ImageNotFoundException();
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
        $imageFilepath = $this->imageLocator->getRelativeImageFilepathFromAttributes(
            $id,
            $extension,
            $entityName,
            $type,
            $sizeName
        );

        return $domainConfig->getUrl() . $this->imageUrlPrefix . $imageFilepath;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $imageOrEntity
     * @param string|null $sizeName
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[]
     */
    public function getAdditionalImagesData(DomainConfig $domainConfig, $imageOrEntity, ?string $sizeName, ?string $type)
    {
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
        ?string $sizeName = null
    ): array {
        $entityConfig = $this->imageConfig->getEntityConfigByEntityName($entityName);
        $sizeConfig = $entityConfig->getSizeConfigByType($type, $sizeName);

        $result = [];
        foreach ($sizeConfig->getAdditionalSizes() as $additionalSizeIndex => $additionalSizeConfig) {
            $imageFilepath = $this->imageLocator->getRelativeImageFilepathFromAttributes(
                $id,
                $extension,
                $entityName,
                $type,
                $sizeName,
                $additionalSizeIndex
            );
            $url = $domainConfig->getUrl() . $this->imageUrlPrefix . $imageFilepath;

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
    protected function getAdditionalImageUrl(DomainConfig $domainConfig, int $additionalSizeIndex, Image $image, ?string $sizeName)
    {
        if ($this->imageLocator->imageExists($image)) {
            return $domainConfig->getUrl()
                . $this->imageUrlPrefix
                . $this->imageLocator->getRelativeAdditionalImageFilepath($image, $additionalSizeIndex, $sizeName);
        }

        throw new ImageNotFoundException();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|object $imageOrEntity
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image
     */
    public function getImageByObject($imageOrEntity, $type = null)
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
    public function getById($imageId)
    {
        return $this->imageRepository->getById($imageId);
    }

    /**
     * @param object $sourceEntity
     * @param object $targetEntity
     */
    public function copyImages($sourceEntity, $targetEntity)
    {
        $sourceImages = $this->getAllImagesByEntity($sourceEntity);
        $targetImages = [];
        foreach ($sourceImages as $sourceImage) {
            try {
                $this->mountManager->copy(
                    'main://' . $this->imageLocator->getAbsoluteImageFilepath(
                        $sourceImage,
                        ImageConfig::ORIGINAL_SIZE_NAME
                    ),
                    'main://' . TransformString::removeDriveLetterFromPath(
                        $this->fileUpload->getTemporaryFilepath($sourceImage->getFilename())
                    )
                );
            } catch (FileNotFoundException $exception) {
                $this->logger->error('Image could not be copied because file was not found', [$exception]);
                continue;
            }

            $targetImage = $this->imageFactory->create(
                $this->imageConfig->getImageEntityConfig($targetEntity)->getEntityName(),
                $this->getEntityId($targetEntity),
                $sourceImage->getType(),
                $sourceImage->getFilename()
            );

            $this->em->persist($targetImage);
            $targetImages[] = $targetImage;
        }
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $orderedImages
     */
    protected function setImagePositionsByOrder($orderedImages)
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
}
