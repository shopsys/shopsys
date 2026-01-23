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
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ImageFacade $imageFacade,
        protected readonly ImageUrlWithSizeHelper $imageUrlWithSizeHelper,
    ) {
    }

    public function create(Brand $brand, DomainConfig $domainConfig): LuigisBoxBrandFeedItem
    {
        try {
            $imageUrl = $this->imageUrlWithSizeHelper->limitSizeInImageUrl($this->imageFacade->getImageUrl($domainConfig, $brand), 100, 100);
        } catch (ImageNotFoundException) {
            $imageUrl = null;
        }

        $domainId = $domainConfig->getId();

        return new LuigisBoxBrandFeedItem(
            $brand->getId(),
            $brand->getName(),
            $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId($domainId, 'front_brand_detail', $brand->getId()),
            $imageUrl,
            $brand->getSeoTitle($domainId),
            $brand->getSeoMetaDescription($domainId),
            $brand->getSeoH1($domainId),
        );
    }
}
