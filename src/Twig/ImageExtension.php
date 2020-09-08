<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\ReadModelBundle\Twig\ImageExtension as BaseImageExtension;
use Twig\TwigFunction;

/**
 * @property \App\Component\Domain\Domain $domain
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method \App\Component\Image\Image[] getImages(object $entity, string|null $type)
 * @method __construct(string $frontDesignImageUrlPrefix, \App\Component\Domain\Domain $domain, \App\Component\Image\ImageLocator $imageLocator, \App\Component\Image\ImageFacade $imageFacade, \Twig\Environment $twigEnvironment, bool $isLazyLoadEnabled)
 * @method bool imageExists(\App\Component\Image\Image|object $imageOrEntity, string|null $type)
 * @method string getImageUrl(\App\Component\Image\Image|object $imageOrEntity, string|null $sizeName, string|null $type)
 * @property \App\Component\Image\ImageLocator $imageLocator
 */
class ImageExtension extends BaseImageExtension
{
    public const NOIMAGE_FILENAME = parent::NOIMAGE_FILENAME;
    public const OPTIMIZED_NOIMAGE_FILENAME = 'optimized-' . parent::NOIMAGE_FILENAME;

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        $twigFunctions = parent::getFunctions();

        $twigFunctions[] = new TwigFunction('productImageUrl', [$this, 'getProductImageUrl']);

        $twigFunctions[] = new TwigFunction('productImageByAkeneoType', [$this, 'getProductImageHtmlByAkeneoType'], ['is_safe' => ['html']]);

        $twigFunctions[] = new TwigFunction('existProductImageByAkeneoType', [$this, 'isProductImageHtmlByAkeneoType']);

        return $twigFunctions;
    }

    /**
     * @param int $productId
     * @param string|null $sizeName
     * @param string|null $type
     * @return string
     */
    public function getProductImageUrl(int $productId, ?string $sizeName = null, ?string $type = null): string
    {
        try {
            return $this->imageFacade->getProductImageUrlByProductId($productId, $this->domain->getCurrentDomainConfig(), $sizeName, $type);
        } catch (ImageNotFoundException $e) {
            return $this->getEmptyImageUrl();
        }
    }

    /**
     * @param \App\Model\Product\Product $entity
     * @param string $akeneoImageType
     * @param array $attributes
     * @return string
     */
    public function getProductImageHtmlByAkeneoType(Product $entity, string $akeneoImageType, array $attributes = [])
    {
        $this->preventDefault($attributes);

        try {
            /** @var \App\Component\Image\Image $image */
            $image = $this->imageFacade->getImageByObjectAndAkeneoType($entity, $akeneoImageType);
            $entityName = $image->getEntityName();
            $attributes['src'] = $this->getImageUrl($image, $attributes['size'], $attributes['type']);
            $additionalImagesData = $this->imageFacade->getAdditionalImagesData($this->domain->getCurrentDomainConfig(), $image, $attributes['size'], $attributes['type']);

            return $this->getImageHtmlByEntityName($attributes, $entityName, $additionalImagesData);
        } catch (ImageNotFoundException $e) {
            return '';
        }
    }

    /**
     * @param \App\Model\Product\Product $entity
     * @param string $akeneoImageType
     * @return bool
     */
    public function isProductImageHtmlByAkeneoType(Product $entity, string $akeneoImageType): bool
    {
        try {
            $this->imageFacade->getImageByObjectAndAkeneoType($entity, $akeneoImageType);
            return true;
        } catch (ImageNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    protected function getEmptyImageUrl(): string
    {
        return $this->imageFacade->getEmptyImageUrl(parent::getEmptyImageUrl());
    }

    /**
     * @param \App\Component\Image\Image|\Shopsys\ReadModelBundle\Image\ImageView|Object|null $imageView
     * @param array $attributes
     * @return string
     */
    public function getImageHtml($imageView, array $attributes = []): string
    {
        try {
            return parent::getImageHtml($imageView, $attributes);
        } catch (ImageNotFoundException $e) {
            return $this->getNoimageHtml($attributes);
        }
    }
}
