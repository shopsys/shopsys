<?php

declare(strict_types=1);

namespace App\Model\Sitemap;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapFacade as BaseSitemapFacade;

/**
 * @property \App\Model\Sitemap\SitemapRepository $sitemapRepository
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method __construct(mixed $sitemapsDir, mixed $sitemapsUrlPrefix, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumperFactory $domainSitemapDumperFactory, \App\Model\Sitemap\SitemapRepository $sitemapRepository, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade)
 */
class SitemapFacade extends BaseSitemapFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForSoldOutProducts(DomainConfig $domainConfig): array
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());

        return $this->sitemapRepository->getSitemapItemsForSoldOutProducts($domainConfig, $pricingGroup);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForBlogArticlesOnDomain(DomainConfig $domainConfig): array
    {
        return $this->sitemapRepository->getSitemapItemsForBlogArticlesOnDomain($domainConfig);
    }
}
