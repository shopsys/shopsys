<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class SitemapFacade
{
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
            if ($this->domain->isMainDomainWithinSameBaseUrlGroup($domainConfig) === false) {
                continue;
            }

            $section = (string)$domainConfig->getId();

            $domainSitemapDumper = $this->domainSitemapDumperFactory->createForDomain($domainConfig->getId());
            $domainSitemapDumper->dump(
                $this->sitemapsDir,
                $domainConfig->getBaseUrl() . $this->sitemapsUrlPrefix . '/',
                $section,
            );
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForListableProducts(DomainConfig $domainConfig): array
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());

        return $this->sitemapRepository->getSitemapItemsForListableProducts($domainConfig, $pricingGroup);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleCategories(DomainConfig $domainConfig): array
    {
        return $this->sitemapRepository->getSitemapItemsForVisibleCategories($domainConfig);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForArticlesOnDomain(DomainConfig $domainConfig): array
    {
        return $this->sitemapRepository->getSitemapItemsForArticlesOnDomain($domainConfig);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForSoldOutProducts(DomainConfig $domainConfig): array
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());

        return $this->sitemapRepository->getSitemapItemsForSoldOutProducts($domainConfig, $pricingGroup);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForBlogArticlesOnDomain(DomainConfig $domainConfig): array
    {
        return $this->sitemapRepository->getSitemapItemsForBlogArticlesOnDomain($domainConfig);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleFlags(DomainConfig $domainConfig): array
    {
        return $this->sitemapRepository->getSitemapItemsForVisibleFlags($domainConfig);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleCategorySeoMix(DomainConfig $domainConfig): array
    {
        return $this->sitemapRepository->getSitemapItemsForVisibleCategorySeoMix($domainConfig);
    }

    public function getSectionNameForDomainConfig(string $sectionName, DomainConfig $domainConfig): string
    {
        if ($domainConfig->getPostfix() !== null) {
            $sectionName .= '_' . trim($domainConfig->getPostfix(), '/');
        }

        return $sectionName;
    }
}
