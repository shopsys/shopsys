<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class SitemapFacade
{
    /**
     * @param string $sitemapsDir
     * @param string $sitemapsUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumperFactory $domainSitemapDumperFactory
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapRepository $sitemapRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(
        protected readonly string $sitemapsDir,
        protected readonly string $sitemapsUrlPrefix,
        protected readonly Domain $domain,
        protected readonly SitemapDumperFactory $domainSitemapDumperFactory,
        protected readonly SitemapRepository $sitemapRepository,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
    ) {
    }

    public function generateForAllDomains(): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $section = (string)$domainConfig->getId();

            $domainSitemapDumper = $this->domainSitemapDumperFactory->createForDomain($domainConfig->getId());
            $domainSitemapDumper->dump(
                $this->sitemapsDir,
                $domainConfig->getUrl() . $this->sitemapsUrlPrefix . '/',
                $section,
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForListableProducts(DomainConfig $domainConfig): array
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());

        return $this->sitemapRepository->getSitemapItemsForListableProducts($domainConfig, $pricingGroup);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleCategories(DomainConfig $domainConfig): array
    {
        return $this->sitemapRepository->getSitemapItemsForVisibleCategories($domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForArticlesOnDomain(DomainConfig $domainConfig): array
    {
        return $this->sitemapRepository->getSitemapItemsForArticlesOnDomain($domainConfig);
    }

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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleFlags(DomainConfig $domainConfig): array
    {
        return $this->sitemapRepository->getSitemapItemsForVisibleFlags($domainConfig);
    }
}
