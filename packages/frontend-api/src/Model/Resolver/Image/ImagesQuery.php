<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageSizeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Component\Image\ImageFacade as FrontendApiImageFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Image\Exception\ImageSizeInvalidUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Image\Exception\ImageTypeInvalidUserError;

class ImagesQuery extends AbstractQuery
{
    protected const IMAGE_ENTITY_PRODUCT = 'product';
    protected const IMAGE_ENTITY_ADVERT = 'noticer';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageFacade $frontendApiImageFacade
     */
    public function __construct(
        protected readonly ImageFacade $imageFacade,
        protected readonly ImageConfig $imageConfig,
        protected readonly Domain $domain,
        protected readonly FrontendApiImageFacade $frontendApiImageFacade
    ) {
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function imagesByEntityQuery(object $entity, ?string $type, ?string $size): array
    {
        $entityName = $this->imageConfig->getEntityName($entity);

        return $this->resolveByEntityId($entity->getId(), $entityName, $type, $size);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function imagesByProductQuery($data, ?string $type, ?string $size): array
    {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];

        return $this->resolveByEntityId($productId, static::IMAGE_ENTITY_PRODUCT, $type, $size);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\Advert $advert
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function imagesByAdvertQuery(Advert $advert, ?string $type, ?string $size): array
    {
        $entityName = $this->imageConfig->getEntityName($advert);

        return $this->getResolvedImages(
            $this->frontendApiImageFacade->getImagesByEntityIdAndNameIndexedById(
                $advert->getId(),
                $entityName,
                $type
            ),
            $this->getSizeConfigsForAdvert($advert, $type, $size)
        );
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    protected function resolveByEntityId(int $entityId, string $entityName, ?string $type, ?string $size): array
    {
        $sizeConfigs = $this->getSizeConfigs($type, $size, $entityName);
        $images = $this->frontendApiImageFacade->getImagesByEntityIdAndNameIndexedById($entityId, $entityName, $type);

        return $this->getResolvedImages($images, $sizeConfigs);
    }

    /**
     * @param string|null $type
     * @param string|null $size
     * @param string $entityName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    protected function getSizeConfigs(?string $type, ?string $size, string $entityName): array
    {
        $imageConfig = $this->imageConfig->getEntityConfigByEntityName($entityName);

        if ($size === ImageConfig::DEFAULT_SIZE_NAME) {
            $size = ImageEntityConfig::WITHOUT_NAME_KEY;
        }

        try {
            if ($type === null) {
                if ($size === null) {
                    $sizeConfigs = $imageConfig->getSizeConfigs();
                } else {
                    $sizeConfigs = [$imageConfig->getSizeConfig($size)];
                }
            } else {
                if ($size === null) {
                    $sizeConfigs = $imageConfig->getSizeConfigsByType($type);
                } else {
                    $sizeConfigs = [$imageConfig->getSizeConfigByType($type, $size)];
                }
            }
        } catch (ImageSizeNotFoundException $e) {
            throw new ImageSizeInvalidUserError(sprintf('Image size %s not found for %s', $size, $entityName));
        } catch (ImageTypeNotFoundException $e) {
            throw new ImageTypeInvalidUserError(sprintf('Image type %s not found for %s', $type, $entityName));
        }

        return $sizeConfigs;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $images
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizeConfigs
     * @return array
     */
    protected function getResolvedImages(array $images, array $sizeConfigs): array
    {
        $resolvedImages = [];

        foreach ($images as $image) {
            foreach ($sizeConfigs as $sizeConfig) {
                $resolvedImages[] = $this->getResolvedImage($image, $sizeConfig);
            }
        }

        return $resolvedImages;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig $sizeConfig
     * @return array
     */
    protected function getResolvedImage(Image $image, ImageSizeConfig $sizeConfig): array
    {
        return [
            'name' => $image->getName(),
            'type' => $image->getType(),
            'position' => $image->getPosition(),
            'width' => $sizeConfig->getWidth(),
            'height' => $sizeConfig->getHeight(),
            'size' => $sizeConfig->getName() === null ? ImageConfig::DEFAULT_SIZE_NAME : $sizeConfig->getName(),
            'url' => $this->imageFacade->getImageUrl(
                $this->domain->getCurrentDomainConfig(),
                $image,
                $sizeConfig->getName(),
                $image->getType()
            ),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\Advert $advert
     * @param string|null $type
     * @param string|null $size
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    protected function getSizeConfigsForAdvert(Advert $advert, ?string $type, ?string $size): array
    {
        $entityName = static::IMAGE_ENTITY_ADVERT;
        if ($size === null) {
            return array_merge(
                $this->getSizeConfigs($type, $advert->getPositionName(), $entityName),
                $this->getSizeConfigs($type, ImageConfig::ORIGINAL_SIZE_NAME, $entityName)
            );
        }

        return $this->getSizeConfigs($type, $size, $entityName);
    }
}
