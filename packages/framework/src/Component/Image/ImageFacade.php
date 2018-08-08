<?php

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

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
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageService
     */
    protected $imageService;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

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

    public function __construct(
        $imageUrlPrefix,
        EntityManagerInterface $em,
        ImageConfig $imageConfig,
        ImageRepository $imageRepository,
        ImageService $imageService,
        FilesystemInterface $filesystem,
        FileUpload $fileUpload,
        ImageLocator $imageLocator
    ) {
        $this->imageUrlPrefix = $imageUrlPrefix;
        $this->em = $em;
        $this->imageConfig = $imageConfig;
        $this->imageRepository = $imageRepository;
        $this->imageService = $imageService;
        $this->filesystem = $filesystem;
        $this->fileUpload = $fileUpload;
        $this->imageLocator = $imageLocator;
    }

    /**
     * @param array|null $temporaryFilenames
     * @param string|null $type
     */
    public function uploadImage(object $entity, ?array $temporaryFilenames, ?string $type): void
    {
        if ($temporaryFilenames !== null && count($temporaryFilenames) > 0) {
            $entitiesForFlush = [];
            $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);
            $entityId = $this->getEntityId($entity);
            $oldImage = $this->imageRepository->findImageByEntity($imageEntityConfig->getEntityName(), $entityId, $type);

            if ($oldImage !== null) {
                $this->em->remove($oldImage);
                $entitiesForFlush[] = $oldImage;
            }

            $newImage = $this->imageService->createImage(
                $imageEntityConfig,
                $entityId,
                array_pop($temporaryFilenames),
                $type
            );
            $this->em->persist($newImage);
            $entitiesForFlush[] = $newImage;

            $this->em->flush($entitiesForFlush);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $orderedImages
     */
    public function saveImageOrdering($orderedImages): void
    {
        $this->imageService->setImagePositionsByOrder($orderedImages);
        $this->em->flush($orderedImages);
    }

    /**
     * @param array|null $temporaryFilenames
     * @param string|null $type
     */
    public function uploadImages(object $entity, ?array $temporaryFilenames, ?string $type): void
    {
        if ($temporaryFilenames !== null && count($temporaryFilenames) > 0) {
            $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);
            $entityId = $this->getEntityId($entity);

            $images = $this->imageService->getUploadedImages($imageEntityConfig, $entityId, $temporaryFilenames, $type);
            foreach ($images as $image) {
                $this->em->persist($image);
            }
            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $images
     */
    public function deleteImages(object $entity, array $images): void
    {
        $entityName = $this->imageConfig->getEntityName($entity);
        $entityId = $this->getEntityId($entity);

        // files will be deleted in doctrine listener
        $this->imageService->deleteImages($entityName, $entityId, $images);

        foreach ($images as $image) {
            $this->em->remove($image);
        }
    }

    /**
     * @param string|null $type
     */
    public function getImageByEntity(object $entity, ?string $type): \Shopsys\FrameworkBundle\Component\Image\Image
    {
        return $this->imageRepository->getImageByEntity(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $type
        );
    }

    /**
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImagesByEntityIndexedById(object $entity, ?string $type): array
    {
        return $this->imageRepository->getImagesByEntityIndexedById(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $type
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getAllImagesByEntity(object $entity): array
    {
        return $this->imageRepository->getAllImagesByEntity(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity)
        );
    }

    public function deleteImageFiles(Image $image): void
    {
        $entityName = $image->getEntityName();
        $imageConfig = $this->imageConfig->getEntityConfigByEntityName($entityName);
        foreach ($imageConfig->getSizeConfigs() as $sizeConfig) {
            $filepath = $this->imageLocator->getAbsoluteImageFilepath($image, $sizeConfig->getName());

            if ($this->filesystem->has($filepath)) {
                $this->filesystem->delete($filepath);
            }
        }
    }
    
    protected function getEntityId(object $entity): int
    {
        $entityMetadata = $this->em->getClassMetadata(get_class($entity));
        $identifier = $entityMetadata->getIdentifierValues($entity);
        if (count($identifier) === 1) {
            return array_pop($identifier);
        }

        $message = 'Entity "' . get_class($entity) . '" has not set primary key or primary key is compound."';
        throw new \Shopsys\FrameworkBundle\Component\Image\Exception\EntityIdentifierException($message);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[]
     */
    public function getAllImageEntityConfigsByClass(): array
    {
        return $this->imageConfig->getAllImageEntityConfigsByClass();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|Object $imageOrEntity
     * @param string|null $sizeName
     * @param string|null $type
     */
    public function getImageUrl(DomainConfig $domainConfig, $imageOrEntity, $sizeName = null, $type = null): string
    {
        $image = $this->getImageByObject($imageOrEntity, $type);
        if ($this->imageLocator->imageExists($image)) {
            return $domainConfig->getUrl()
                . $this->imageUrlPrefix
                . $this->imageLocator->getRelativeImageFilepath($image, $sizeName);
        }

        throw new \Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|Object $imageOrEntity
     * @param string|null $type
     */
    public function getImageByObject($imageOrEntity, $type = null): \Shopsys\FrameworkBundle\Component\Image\Image
    {
        if ($imageOrEntity instanceof Image) {
            return $imageOrEntity;
        } else {
            return $this->getImageByEntity($imageOrEntity, $type);
        }
    }
    
    public function getById(int $imageId): \Shopsys\FrameworkBundle\Component\Image\Image
    {
        return $this->imageRepository->getById($imageId);
    }
    
    public function copyImages(object $sourceEntity, object $targetEntity): void
    {
        $sourceImages = $this->getAllImagesByEntity($sourceEntity);
        $targetImages = [];
        foreach ($sourceImages as $sourceImage) {
            $this->filesystem->copy(
                $this->imageLocator->getAbsoluteImageFilepath($sourceImage, ImageConfig::ORIGINAL_SIZE_NAME),
                $this->fileUpload->getTemporaryFilepath($sourceImage->getFilename())
            );

            $targetImage = $this->imageService->createImage(
                $this->imageConfig->getImageEntityConfig($targetEntity),
                $this->getEntityId($targetEntity),
                $sourceImage->getFilename(),
                $sourceImage->getType()
            );

            $this->em->persist($targetImage);
            $targetImages[] = $targetImage;
        }
        $this->em->flush($targetImages);
    }
}
