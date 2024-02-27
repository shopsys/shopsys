<?php

declare(strict_types=1);

namespace Shopsys\BrandFeed\LuigisBoxBundle\Model;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class LuigisBoxBrandFeedItemFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ImageFacade $imageFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\BrandFeed\LuigisBoxBundle\Model\LuigisBoxBrandFeedItem
     */
    public function create(Brand $brand, DomainConfig $domainConfig): LuigisBoxBrandFeedItem
    {
        try {
            $imageUrl = ImageUrlWithSizeHelper::limitSizeInImageUrl($this->imageFacade->getImageUrl($domainConfig, $brand), 100, 100);
        } catch (ImageNotFoundException) {
            $imageUrl = null;
        }

        return new LuigisBoxBrandFeedItem(
            $brand->getId(),
            $brand->getName(),
            $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId($domainConfig->getId(), 'front_brand_detail', $brand->getId()),
            $imageUrl,
        );
    }
}
