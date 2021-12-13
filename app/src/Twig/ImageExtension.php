<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Twig\ImageExtension as BaseImageExtension;
use Twig\TwigFunction;

/**
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method \App\Component\Image\Image[] getImages(object $entity, string|null $type = null)
 * @method __construct(string $frontDesignImageUrlPrefix, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Component\Image\ImageLocator $imageLocator, \App\Component\Image\ImageFacade $imageFacade, \Twig\Environment $twigEnvironment, bool $isLazyLoadEnabled = false)
 * @method bool imageExists(\App\Component\Image\Image|object $imageOrEntity, string|null $type = null)
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

        $twigFunctions[] = new TwigFunction('productImageByIdAndAkeneoType', [$this, 'getProductImageHtmlByIdAndAkeneoType'], ['is_safe' => ['html']]);

        $twigFunctions[] = new TwigFunction('existProductImageByIdAndAkeneoType', [$this, 'isProductImageHtmlByIdAndAkeneoType']);

        return $twigFunctions;
    }

    /**
     * @param int $productId
     * @param string $akeneoImageType
     * @param array $attributes
     * @return string
     */
    public function getProductImageHtmlByIdAndAkeneoType(int $productId, string $akeneoImageType, array $attributes = [])
    {
        $this->preventDefault($attributes);

        try {
            $image = $this->imageFacade->getImageByEntityIdAndAkeneoType($productId, Product::class, $akeneoImageType);
            $entityName = $image->getEntityName();
            $attributes['src'] = $this->getImageUrl($image, $attributes['size'], $attributes['type']);
            $additionalImagesData = $this->imageFacade->getAdditionalImagesData($this->domain->getCurrentDomainConfig(), $image, $attributes['size'], $attributes['type']);

            return $this->getImageHtmlByEntityName($attributes, $entityName, $additionalImagesData);
        } catch (ImageNotFoundException $e) {
            return '';
        }
    }

    /**
     * @param int $productId
     * @param string $akeneoImageType
     * @return bool
     */
    public function isProductImageHtmlByIdAndAkeneoType(int $productId, string $akeneoImageType): bool
    {
        try {
            $this->imageFacade->getImageByEntityIdAndAkeneoType($productId, Product::class, $akeneoImageType);
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
     * @param \App\Component\Image\Image|\Shopsys\ReadModelBundle\Image\ImageView|object|null $imageView
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

    /**
     * @deprecated Method will be moved to framework in the SSFW 10
     * @param array $attributes
     * @param string $entityName
     * @param \App\Component\Image\AdditionalImageData[] $additionalImagesData
     * @return string
     */
    protected function getImageHtmlByEntityName(array $attributes, $entityName, $additionalImagesData = []): string
    {
        $htmlAttributes = $this->extractHtmlAttributesFromAttributes($attributes);

        if ($this->isLazyLoadEnabled($attributes) === true) {
            $htmlAttributes = $this->makeHtmlAttributesLazyLoaded($htmlAttributes);
        }

        return $this->twigEnvironment->render('@ShopsysFramework/Common/image.html.twig', [
            'attr' => $htmlAttributes,
            'additionalImagesData' => $additionalImagesData,
            'imageCssClass' => $this->getImageCssClass($entityName, $attributes['type'], $attributes['size']),
        ]);
    }

    /**
     * @param \App\Component\Image\Image|\Shopsys\ReadModelBundle\Image\ImageView|object $imageView
     * @param string|null $sizeName
     * @param string|null $type
     * @return string
     */
    public function getImageUrl($imageView, $sizeName = null, $type = null)
    {
        if ($imageView instanceof ImageView) {
            $entityName = $imageView->getEntityName();

            try {
                return $this->imageFacade->getImageUrlFromAttributes(
                    $this->domain->getCurrentDomainConfig(),
                    $imageView->getId(),
                    $imageView->getExtension(),
                    $entityName,
                    $type,
                    $sizeName
                );
            } catch (ImageNotFoundException $exception) {
                return $this->getEmptyImageUrl();
            }
        }

        return parent::getImageUrl($imageView, $sizeName, $type);
    }
}
