<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Image;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductImageFacade
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ImageFacade $imageFacade,
    ) {
    }

    /**
     * @throws \Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException
     */
    public function getProductImageUrl(?Product $product, int $domainId): string
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        if ($product === null) {
            return $this->imageFacade->getEmptyImageUrl($domainConfig);
        }

        try {
            $imageUrl = $this->imageFacade->getImageUrl(
                $domainConfig,
                $product,
            );

            return $imageUrl . '?width=100';
        } catch (ImageNotFoundException) {
            return $this->imageFacade->getEmptyImageUrl($domainConfig);
        }
    }
}
