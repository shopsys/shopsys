<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class HreflangLinksFacade
{
    protected const ROUTE_PRODUCT_DETAIL = 'front_product_detail';
    protected const ROUTE_PRODUCT_LIST = 'front_product_list';
    protected const ROUTE_BRAND_DETAIL = 'front_brand_detail';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $currentDomainId
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForProduct(Product $product, int $currentDomainId): array
    {
        $domainIds = $this->getRelevantDomainIds($currentDomainId);

        $result = [];

        foreach ($domainIds as $domainId) {
            $productVisibility = $this->productVisibilityFacade->getProductVisibility(
                $product,
                $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId),
                $domainId,
            );

            if (!$productVisibility->isVisible()) {
                continue;
            }

            $result[] = new HreflangLink(
                $this->domain->getDomainConfigById($domainId)->getLocale(),
                $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                    $domainId,
                    static::ROUTE_PRODUCT_DETAIL,
                    $product->getId(),
                ),
            );
        }

        return $result;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $currentDomainId
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForCategory(Category $category, int $currentDomainId): array
    {
        $domainIds = $this->getRelevantDomainIds($currentDomainId);

        $result = [];

        foreach ($domainIds as $domainId) {
            if ($category->isVisible($domainId) === false) {
                continue;
            }

            $result[] = new HreflangLink(
                $this->domain->getDomainConfigById($domainId)->getLocale(),
                $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                    $domainId,
                    static::ROUTE_PRODUCT_LIST,
                    $category->getId(),
                ),
            );
        }

        return $result;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param int $currentDomainId
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForBrand(Brand $brand, int $currentDomainId): array
    {
        $domainIds = $this->getRelevantDomainIds($currentDomainId);

        $result = [];

        foreach ($domainIds as $domainId) {
            $result[] = new HreflangLink(
                $this->domain->getDomainConfigById($domainId)->getLocale(),
                $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                    $domainId,
                    static::ROUTE_BRAND_DETAIL,
                    $brand->getId(),
                ),
            );
        }

        return $result;
    }

    /**
     * @param int $currentDomainId
     * @return int[]
     */
    protected function getRelevantDomainIds(int $currentDomainId): array
    {
        $alternativeDomainIds = $this->seoSettingFacade->getAlternativeDomainsForDomain($currentDomainId);

        return [$currentDomainId, ...$alternativeDomainIds];
    }
}
