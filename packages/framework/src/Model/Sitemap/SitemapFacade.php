<?php

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class SitemapFacade
{
    protected string $sitemapsDir;

    protected string $sitemapsUrlPrefix;

    /**
     * @param mixed $sitemapsDir
     * @param mixed $sitemapsUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumperFactory $domainSitemapDumperFactory
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapRepository $sitemapRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(
        $sitemapsDir,
        $sitemapsUrlPrefix,
        protected readonly Domain $domain,
        protected readonly SitemapDumperFactory $domainSitemapDumperFactory,
        protected readonly SitemapRepository $sitemapRepository,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade
    ) {
        $this->sitemapsDir = $sitemapsDir;
        $this->sitemapsUrlPrefix = $sitemapsUrlPrefix;
    }

    public function generateForAllDomains()
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $section = (string)$domainConfig->getId();

            $domainSitemapDumper = $this->domainSitemapDumperFactory->createForDomain($domainConfig->getId());
            $domainSitemapDumper->dump(
                $this->sitemapsDir,
                $domainConfig->getUrl() . $this->sitemapsUrlPrefix . '/',
                $section
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForListableProducts(DomainConfig $domainConfig)
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());

        return $this->sitemapRepository->getSitemapItemsForListableProducts($domainConfig, $pricingGroup);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleCategories(DomainConfig $domainConfig)
    {
        return $this->sitemapRepository->getSitemapItemsForVisibleCategories($domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForArticlesOnDomain(DomainConfig $domainConfig)
    {
        return $this->sitemapRepository->getSitemapItemsForArticlesOnDomain($domainConfig);
    }
}
