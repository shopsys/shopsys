<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;

class HreflangLinksFacade
{
    protected const string ROUTE_PRODUCT_DETAIL = 'front_product_detail';
    protected const string ROUTE_PRODUCT_LIST = 'front_product_list';
    protected const string ROUTE_BRAND_DETAIL = 'front_brand_detail';
    protected const string ROUTE_FLAG_DETAIL = 'front_flag_detail';
    protected const string ROUTE_BLOG_ARTICLE_DETAIL = 'front_blogarticle_detail';
    protected const string ROUTE_BLOG_CATEGORY_DETAIL = 'front_blogcategory_detail';

    public function __construct(
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
    ) {
    }

    public function createHreflangLink(
        int $domainId,
        string $routeName,
        int $entityId,
        bool $absoluteUrl = true,
    ): HreflangLink {
        if ($absoluteUrl === true) {
            $href = $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                $domainId,
                $routeName,
                $entityId,
            );
        } else {
            $href = $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
                $domainId,
                $routeName,
                $entityId,
            );
        }

        return new HreflangLink(
            $this->domain->getDomainConfigById($domainId)->getLocale(),
            $href,
            $domainId,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForProduct(Product $product, int $currentDomainId, bool $absoluteUrl = true): array
    {
        $isVisibleCallable = function (Product $product, int $domainId): bool {
            $productVisibility = $this->productVisibilityFacade->getProductVisibility(
                $product,
                $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId),
                $domainId,
            );

            return $productVisibility->isVisible();
        };

        return $this->doGetHrefLinks(
            $product,
            $currentDomainId,
            static::ROUTE_PRODUCT_DETAIL,
            $absoluteUrl,
            $isVisibleCallable,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForCategory(Category $category, int $currentDomainId, bool $absoluteUrl = true): array
    {
        $isVisibleCallable = static function (Category $category, int $domainId): bool {
            return $category->isVisible($domainId);
        };

        return $this->doGetHrefLinks(
            $category,
            $currentDomainId,
            static::ROUTE_PRODUCT_LIST,
            $absoluteUrl,
            $isVisibleCallable,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForBrand(Brand $brand, int $currentDomainId, bool $absoluteUrl = true): array
    {
        return $this->doGetHrefLinks($brand, $currentDomainId, static::ROUTE_BRAND_DETAIL, $absoluteUrl);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForBlogArticle(BlogArticle $blogArticle, int $currentDomainId, bool $absoluteUrl = true): array
    {
        $isVisibleCallable = static function (BlogArticle $blogArticle, int $domainId): bool {
            return $blogArticle->isVisible($domainId);
        };

        return $this->doGetHrefLinks(
            $blogArticle,
            $currentDomainId,
            static::ROUTE_BLOG_ARTICLE_DETAIL,
            $absoluteUrl,
            $isVisibleCallable,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForBlogCategory(
        BlogCategory $blogCategory,
        int $currentDomainId,
        bool $absoluteUrl = true,
    ): array {
        $isVisibleCallable = static function (BlogCategory $blogCategory, int $domainId): bool {
            return $blogCategory->isVisible($domainId);
        };

        return $this->doGetHrefLinks(
            $blogCategory,
            $currentDomainId,
            static::ROUTE_BLOG_CATEGORY_DETAIL,
            $absoluteUrl,
            $isVisibleCallable,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForFlag(Flag $flag, int $currentDomainId, bool $absoluteUrl = true): array
    {
        return $this->doGetHrefLinks($flag, $currentDomainId, static::ROUTE_FLAG_DETAIL, $absoluteUrl);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForSeoPage(SeoPage $seoPage, int $currentDomainId, bool $absoluteUrl = true): array
    {
        return $this->doGetHrefLinks($seoPage, $currentDomainId, '', $absoluteUrl);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    protected function doGetHrefLinks(
        BlogArticle|BlogCategory|Brand|Category|Product|SeoPage|Flag $entity,
        int $currentDomainId,
        string $routeName,
        bool $absoluteUrl,
        ?callable $isVisibleCallable = null,
    ): array {
        $domainIds = $this->getRelevantDomainIds($currentDomainId);

        $result = [];

        foreach ($domainIds as $domainId) {
            if ($isVisibleCallable !== null && $isVisibleCallable($entity, $domainId) === false) {
                continue;
            }

            if ($entity instanceof SeoPage) {
                $result[] = $this->createHreflangLinkForSeoPage($domainId, $entity, $absoluteUrl);
            } else {
                $result[] = $this->createHreflangLink($domainId, $routeName, $entity->getId(), $absoluteUrl);
            }
        }

        return $result;
    }

    /**
     * @return int[]
     */
    protected function getRelevantDomainIds(int $currentDomainId): array
    {
        $alternativeDomainIds = $this->seoSettingFacade->getAlternativeDomainsForDomain($currentDomainId);

        return [$currentDomainId, ...$alternativeDomainIds];
    }

    /**
     * @throws \Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException
     */
    protected function createHreflangLinkForSeoPage(int $domainId, SeoPage $seoPage, bool $absoluteUrl): HreflangLink
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);
        $href = ($absoluteUrl === true ? $domainConfig->getUrl() : '') . '/';
        $pageSlug = $seoPage->getPageSlug($domainId);

        if ($pageSlug !== SeoPage::SEO_PAGE_HOMEPAGE_SLUG) {
            $href .= $pageSlug;
        }

        return new HreflangLink(
            $domainConfig->getLocale(),
            $href,
            $domainId,
        );
    }

    /**
     * @param array<array{hreflang: string, href: string, domainId: int}> $hreflangLinks
     * @return array<array{hreflang: string, href: string, domainId: int}>
     */
    public function getHreflangLinksWithIncludedDomainUrl(array $hreflangLinks): array
    {
        foreach ($hreflangLinks as $key => $hreflangLink) {
            if (str_starts_with($hreflangLink['href'], 'http')) {
                continue;
            }

            // @deprecated - fallback for not yet exported data to Elasticsearch
            $domainId = array_key_exists('domainId', $hreflangLink) ? $hreflangLink['domainId'] : $this->getRelevantDomainIds($this->domain->getId())[$key];

            $hreflangLinks[$key]['href'] = $this->domain->getDomainConfigById($domainId)->getUrl() . '/' . $hreflangLink['href'];
        }

        return $hreflangLinks;
    }
}
