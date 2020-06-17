<?php

declare(strict_types=1);

namespace App\Twig;

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
}
