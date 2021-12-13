<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Image;

use App\Component\Image\ImageFacade;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageSizeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrontendApiBundle\Component\Image\ImageFacade as FrontendApiImageFacade;

abstract class AbstractImagesResolver implements ResolverInterface
{
    /**
     * @var \App\Component\Image\ImageFacade
     */
    private ImageFacade $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     */
    private ImageConfig $imageConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrontendApiBundle\Component\Image\ImageFacade
     */
    protected FrontendApiImageFacade $frontendApiImageFacade;

    /**
     * @var string
     */
    protected static string $entityName;

    /**
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageFacade $frontendApiImageFacade
     */
    public function __construct(
        ImageFacade $imageFacade,
        ImageConfig $imageConfig,
        Domain $domain,
        FrontendApiImageFacade $frontendApiImageFacade
    ) {
        $this->imageFacade = $imageFacade;
        $this->imageConfig = $imageConfig;
        $this->domain = $domain;
        $this->frontendApiImageFacade = $frontendApiImageFacade;
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @param array|null $sizes
     * @return array
     */
    public function resolveByEntity(object $entity, ?string $type, ?array $sizes): array
    {
        return $this->resolveByEntityId($entity->getId(), static::$entityName, $type, $sizes);
    }

    /**
     * @param \App\Component\Image\Image[] $images
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizeConfigs
     * @return array
     */
    protected function getResolvedImages(array $images, array $sizeConfigs): array
    {
        $resolvedImages = [];

        foreach ($images as $image) {
            $imageSizes = [];
            foreach ($sizeConfigs as $sizeConfig) {
                try {
                    $imageSizes[] = $this->getResolvedImage($image, $sizeConfig);
                } catch (ImageNotFoundException $exception) {
                    continue;
                }
            }

            if ($imageSizes === []) {
                continue;
            }

            $resolvedImages[] = [
                'position' => $image->getPosition(),
                'type' => $image->getType(),
                'sizes' => $imageSizes,
            ];
        }

        return $resolvedImages;
    }

    /**
     * @param \App\Component\Image\Image $image
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig $sizeConfig
     * @return array
     */
    private function getResolvedImage(Image $image, ImageSizeConfig $sizeConfig): array
    {
        return [
            'width' => $sizeConfig->getWidth(),
            'height' => $sizeConfig->getHeight(),
            'size' => $sizeConfig->getName() ?? ImageConfig::DEFAULT_SIZE_NAME,
            'url' => $this->imageFacade->getImageUrl(
                $this->domain->getCurrentDomainConfig(),
                $image,
                $sizeConfig->getName(),
                $image->getType()
            ),
            'additionalSizes' => $this->imageFacade->getAdditionalImagesData(
                $this->domain->getCurrentDomainConfig(),
                $image,
                $sizeConfig->getName(),
                $image->getType()
            ),
        ];
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @param array|null $sizes
     * @return array
     */
    protected function resolveByEntityId(int $entityId, string $entityName, ?string $type, ?array $sizes): array
    {
        $sizeConfigs = $this->getSizeConfigs($type, $sizes, $entityName);
        /** @var \App\Component\Image\Image[] $images */
        $images = $this->frontendApiImageFacade->getImagesByEntityIdAndNameIndexedById($entityId, $entityName, $type);

        return $this->getResolvedImages($images, $sizeConfigs);
    }

    /**
     * @param string|null $type
     * @param array|null $sizes
     * @param string $entityName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    protected function getSizeConfigs(?string $type, ?array $sizes, string $entityName): array
    {
        $imageConfig = $this->imageConfig->getEntityConfigByEntityName($entityName);

        if ($sizes === []) {
            $sizes = null;
        }

        if ($sizes === null && $type === null) {
            return $imageConfig->getSizeConfigs();
        }

        if ($sizes === null) {
            try {
                return $imageConfig->getSizeConfigsByType($type);
            } catch (ImageTypeNotFoundException $e) {
                throw new UserError(sprintf('Image type "%s" not found for %s', $type, $entityName));
            }
        }

        $imageSizeConfigs = [];
        foreach ($sizes as $size) {
            $imageSizeConfigs[] = $this->getSingleSizeConfig($imageConfig, $type, $size, $entityName);
        }

        return $imageSizeConfigs;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig $imageConfig
     * @param string|null $type
     * @param string $size
     * @param string $entityName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
     */
    private function getSingleSizeConfig(ImageEntityConfig $imageConfig, ?string $type, string $size, string $entityName): ImageSizeConfig
    {
        try {
            if ($size === ImageConfig::DEFAULT_SIZE_NAME) {
                $size = ImageEntityConfig::WITHOUT_NAME_KEY;
            }

            if ($type === null) {
                return $imageConfig->getSizeConfig($size);
            }

            return $imageConfig->getSizeConfigByType($type, $size);
        } catch (ImageSizeNotFoundException $e) {
            throw new UserError(sprintf('Image size "%s" not found for %s', $size, $entityName));
        } catch (ImageTypeNotFoundException $e) {
            throw new UserError(sprintf('Image type "%s" not found for %s', $type, $entityName));
        }
    }
}
