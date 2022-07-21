<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageSizeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrontendApiBundle\Component\Image\ImageFacade as FrontendApiImageFacade;

class ImagesResolver implements QueryInterface
{
    protected const IMAGE_ENTITY_PRODUCT = 'product';
    protected const IMAGE_ENTITY_CATEGORY = 'category';
    protected const IMAGE_ENTITY_PAYMENT = 'payment';
    protected const IMAGE_ENTITY_TRANSPORT = 'transport';
    protected const IMAGE_ENTITY_BRAND = 'brand';
    protected const IMAGE_ENTITY_ADVERT = 'noticer';

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
     * @var \Shopsys\FrontendApiBundle\Component\Image\ImageFacade
     */
    protected $frontendApiImageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function resolveByProduct($data, ?string $type, ?string $size): array
    {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];
        return $this->resolveByEntityId($productId, static::IMAGE_ENTITY_PRODUCT, $type, $size);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function resolveByCategory(Category $category, ?string $type, ?string $size): array
    {
        return $this->resolveByEntityId($category->getId(), static::IMAGE_ENTITY_CATEGORY, $type, $size);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function resolveByPayment(Payment $payment, ?string $type, ?string $size): array
    {
        return $this->resolveByEntityId($payment->getId(), static::IMAGE_ENTITY_PAYMENT, $type, $size);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function resolveByTransport(Transport $transport, ?string $type, ?string $size): array
    {
        return $this->resolveByEntityId($transport->getId(), static::IMAGE_ENTITY_TRANSPORT, $type, $size);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function resolveByBrand(Brand $brand, ?string $type, ?string $size): array
    {
        return $this->resolveByEntityId($brand->getId(), static::IMAGE_ENTITY_BRAND, $type, $size);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\Advert $advert
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function resolveByAdvert(Advert $advert, ?string $type, ?string $size): array
    {
        return $this->getResolvedImages(
            $this->frontendApiImageFacade->getImagesByEntityIdAndNameIndexedById(
                $advert->getId(),
                static::IMAGE_ENTITY_ADVERT,
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
            throw new UserError(sprintf('Image size %s not found for %s', $size, $entityName));
        } catch (ImageTypeNotFoundException $e) {
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
