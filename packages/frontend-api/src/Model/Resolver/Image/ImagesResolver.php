<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ImagesResolver implements ResolverInterface
{
    protected const IMAGE_ENTITY_PRODUCT = 'product';
    protected const IMAGE_ENTITY_CATEGORY = 'category';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     */
    protected $imageConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ImageFacade $imageFacade,
        ImageConfig $imageConfig,
        Domain $domain
    ) {
        $this->imageFacade = $imageFacade;
        $this->imageConfig = $imageConfig;
        $this->domain = $domain;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function resolveByProduct(Product $product, ?string $type, ?string $size): array
    {
        return $this->resolveByEntity($product, static::IMAGE_ENTITY_PRODUCT, $type, $size);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function resolveByCategory(Category $category, ?string $type, ?string $size): array
    {
        return $this->resolveByEntity($category, static::IMAGE_ENTITY_CATEGORY, $type, $size);
    }

    /**
     * @param object $entity
     * @param string $entityName
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    protected function resolveByEntity(object $entity, string $entityName, ?string $type, ?string $size): array
    {
        $sizeConfigs = $this->getSizeConfigs($type, $size, $entityName);
        $images = $this->imageFacade->getImagesByEntityIndexedById($entity, $type);

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
        } catch (\Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageSizeNotFoundException $e) {
            throw new UserError(sprintf('Image size %s not found for %s', $size, $entityName));
        } catch (\Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException $e) {
            throw new UserError(sprintf('Image type %s not found for %s', $type, $entityName));
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
                $resolvedImages[] = [
                    'type' => $image->getType(),
                    'position' => $image->getPosition(),
                    'width' => $sizeConfig->getWidth(),
                    'height' => $sizeConfig->getHeight(),
                    'size' => $sizeConfig->getName() === null ? ImageConfig::DEFAULT_SIZE_NAME : $sizeConfig->getName(),
                    'url' => $this->imageFacade->getImageUrl($this->domain->getCurrentDomainConfig(), $image, $sizeConfig->getName(), $image->getType()),
                ];
            }
        }

        return $resolvedImages;
    }
}
