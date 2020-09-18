<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use BadMethodCallException;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrontendApiBundle\Component\Image\ImageFacade as FrontendApiImageFacade;

class ImagesResolver implements ResolverInterface
{
    protected const IMAGE_ENTITY_PRODUCT = 'product';
    protected const IMAGE_ENTITY_CATEGORY = 'category';
    protected const IMAGE_ENTITY_PAYMENT = 'payment';
    protected const IMAGE_ENTITY_TRANSPORT = 'transport';

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
     * @var \Shopsys\FrontendApiBundle\Component\Image\ImageFacade|null
     */
    protected $frontendApiImageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageFacade|null $frontendApiImageFacade
     */
    public function __construct(
        ImageFacade $imageFacade,
        ImageConfig $imageConfig,
        Domain $domain,
        ?FrontendApiImageFacade $frontendApiImageFacade = null
    ) {
        $this->imageFacade = $imageFacade;
        $this->imageConfig = $imageConfig;
        $this->domain = $domain;
        $this->frontendApiImageFacade = $frontendApiImageFacade;
    }

    /**
     * @required
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageFacade $frontendApiImageFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setFrontendApiImageFacade(FrontendApiImageFacade $frontendApiImageFacade): void
    {
        if ($this->frontendApiImageFacade !== null && $this->frontendApiImageFacade !== $frontendApiImageFacade) {
            throw new BadMethodCallException(sprintf(
                'Method "%s" has been already called and cannot be called multiple times.',
                __METHOD__
            ));
        }
        if ($this->frontendApiImageFacade === null) {
            @trigger_error(
                sprintf(
                    'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                    __METHOD__
                ),
                E_USER_DEPRECATED
            );

            $this->frontendApiImageFacade = $frontendApiImageFacade;
        }
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
