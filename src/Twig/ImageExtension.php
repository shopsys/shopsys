<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Twig\ImageExtension as BaseImageExtension;
use Twig\TwigFunction;

/**
 * @property \App\Component\Domain\Domain $domain
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method __construct(string $frontDesignImageUrlPrefix, \App\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Component\Image\ImageLocator $imageLocator, \App\Component\Image\ImageFacade $imageFacade, \Twig\Environment $twigEnvironment, bool $isLazyLoadEnabled)
 * @method \App\Component\Image\Image[] getImages(object $entity, string|null $type)
 */
class ImageExtension extends BaseImageExtension
{
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
        } catch (\Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException $e) {
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
        } catch (\Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException $e) {
            return $this->getNoimageHtml($attributes);
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
        } catch (\Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException $e) {
            return false;
        }
    }
}
