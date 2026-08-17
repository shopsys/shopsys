<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\EntityIdentifierException;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ImageFacade
{
    public function __construct(
        protected readonly string $imageUrlPrefix,
        protected readonly EntityManagerInterface $em,
        protected readonly ImageConfig $imageConfig,
        protected readonly ImageRepository $imageRepository,
        protected readonly FilesystemOperator $filesystem,
        protected readonly ImageLocator $imageLocator,
        protected readonly ImageFactory $imageFactory,
        protected readonly CdnFacade $cdnFacade,
        protected readonly CacheInterface|AdapterInterface $cache,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    public function manageImages(object $entity, ImageUploadData $imageUploadData, ?string $type = null): void
    {
        $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);
        $uploadedFiles = $imageUploadData->uploadedFiles;
        $orderedImages = $imageUploadData->orderedImages;
        $imagesToDelete = $imageUploadData->imagesToDelete;

        if ($imageEntityConfig->isMultiple($type) === false) {
            if (count($uploadedFiles) > 0) {
                $imagesToDelete = $orderedImages;
            }

            if (count($orderedImages) > 1) {
                array_shift($orderedImages);
                $imagesToDelete = $orderedImages;
            }
        } else {
            $this->saveImageOrdering($orderedImages);
        }

        $this->saveImagesPathnames($imageUploadData);
        $this->uploadImages($entity, $imageUploadData->uploadedFilenames, $uploadedFiles, $type);

        $this->deleteImages($entity, $imagesToDelete);
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
     * @param array<int, array<string,string>> $namesIndexedByImageIdAndLocale
     * @param array<int, string> $temporaryFilenamesIndexedByImageId
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

        $this->em->flush();
    }

    public function getImageByEntity(object $entity, ?string $type): Image
    {
        return $this->imageRepository->getImageByEntity(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $type,
        );
    }

    /**
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
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getAllImagesByEntity(object $entity): array
    {
        return $this->imageRepository->getAllImagesByEntity(
            $this->imageConfig->getEntityName($entity),
            $this->getEntityId($entity),
        );
    }

    public function deleteImageFiles(Image $image): void
    {
        $filepath = $this->imageLocator->getAbsoluteImageFilepath($image);

        if ($this->filesystem->has($filepath)) {
            $this->filesystem->delete($filepath);
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

        throw new EntityIdentifierException($message);
    }

    public function getImageUrl(
        DomainConfig $domainConfig,
        object $imageOrEntity,
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
            . $this->imageLocator->getRelativeImageFilepathWithSlug($image, $friendlyUrlSeoEntityName);
    }

    public function getEmptyImageUrl(DomainConfig $domainConfig): string
    {
        return $this->cdnFacade->resolveDomainUrlForAssets($domainConfig) . '/public/frontend/images/noimage.png';
    }

    public function getImageByObject(object $imageOrEntity, ?string $type = null): Image
    {
        if ($imageOrEntity instanceof Image) {
            return $imageOrEntity;
        }

        return $this->getImageByEntity($imageOrEntity, $type);
    }

    public function getById(int $imageId): Image
    {
        return $this->imageRepository->getById($imageId);
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

    protected function saveImagesPathnames(ImageUploadData $imageUploadData): void
    {
        foreach ($imageUploadData->namesIndexedByImageIdAndLocale as $imageId => $filenamesIndexedByLocale) {
            $image = $this->getById($imageId);
            $image->setNames($filenamesIndexedByLocale);
        }

        $this->em->flush();
    }

    protected function getCacheIdForImageUrl(
        int $imageId,
        int $domainId,
    ): string {
        return sprintf(
            'ImageUrl_imageId-%d_domainId-%d',
            $imageId,
            $domainId,
        );
    }

    protected function getSeoNameByImageAndLocale(Image $image, string $locale): ?string
    {
        return match ($image->getEntityName()) {
            'category' => $this->em->getRepository(Category::class)->find($image->getEntityId())?->getName($locale),
            'product' => $this->em->getRepository(Product::class)->find($image->getEntityId())?->getName($locale),
            'brand' => $this->em->getRepository(Brand::class)->find($image->getEntityId())?->getName(),
            default => null,
        };
    }

    protected function getFriendlyUrlSlug(?string $seoEntityName): ?string
    {
        if ($seoEntityName === null) {
            return null;
        }

        return $this->transformStringHelper->stringToFriendlyUrlSlug($seoEntityName);
    }
}
